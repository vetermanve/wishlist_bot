<?php


namespace Verse\Scheduler\Provider;


use Closure;
use SebastianBergmann\Type\CallableType;
use Verse\Run\Provider\RequestProviderProto;
use Verse\Run\RunRequest;
use Verse\Scheduler\Service\TimeEventScheduler;
use Verse\Scheduler\Storage\EventsStorage;
use Verse\Scheduler\Storage\TimeSlotStorage;
use Verse\Storage\StorageProto;

class StorageTimeEventsProvider extends RequestProviderProto
{
    /**
     * @var StorageProto
     */
    private StorageProto $timeSlotStorage;

    /**
     * @var StorageProto
     */
    private StorageProto $eventStorage;

    private int $partitionsCount = 1;

    private int $partition = 0;

    private bool $shouldProceed = true;

    /**
     * @var ?Closure
     */
    private ?Closure $shouldProceedCallback;

    public function prepare()
    {
        if (!isset($this->timeSlotStorage)) {
            $this->timeSlotStorage = new TimeSlotStorage();
        }

        if (!isset($this->eventStorage)) {
            $this->eventStorage = new EventsStorage();
        }
    }

    public function run()
    {
        while (call_user_func($this->shouldProceedCallback)) {
            $start = time();;
            $endingTime = time();
            $this->runBlock($endingTime, 30);
            $endingTime -= 30;
            $this->runBlock($endingTime, 300);
            $endingTime -= 300;
            $this->runBlock($endingTime, 3000);
            $timeToNextSecond = $start + 1 - microtime(1);
            if ($timeToNextSecond > 0) {
               usleep(
                   floor($timeToNextSecond*1000)
               );
            }
        };
    }

    public function runBlock($end, $size)
    {
        $timeIds = $this->getTimeRange($end, $size);
        $eventsBySlots = $this->timeSlotStorage->read()->mGet($timeIds, __METHOD__);
        $eventsBySlots = array_filter($eventsBySlots);
        shuffle($eventsBySlots);

        foreach ($eventsBySlots as $eventsBySlot) {
            $events = $this->eventStorage->read()->mGet($eventsBySlot[TimeSlotStorage::EVENT_IDS], __METHOD__);

            foreach ($events as $index => $event) {
                $this->runEvent($event);
            }

            $this->timeSlotStorage->write()->remove($eventsBySlot[TimeSlotStorage::ID], __METHOD__);
        }
    }

    public function getTimeRange($endTime, $step = 30)
    {
        $time = time();
        return range($time - 30 - $this->partition , $time, $this->partitionsCount);
    }

    private function runEvent($event)
    {
        if (isset($event[EventsStorage::TTL]) && $event[EventsStorage::TTL] > 0 // ttl was set
            && ($event[EventsStorage::TIME] + $event[EventsStorage::TTL] < time())) { // and ttl expired
                return false;
        }

        $req = new RunRequest($event[EventsStorage::ID], $event[EventsStorage::ROUTE]);
        $req->data = $event[EventsStorage::DATA];
        $req->params = ['from' => ['user_id' => $event[EventsStorage::USER]]];

        $this->core->process($req);
    }

    /**
     * @return bool
     */
    public function isShouldProceed(): bool
    {
        return $this->shouldProceed;
    }

    /**
     * @return ?Closure
     */
    public function getShouldProceedCallback(): ?Closure
    {
        return $this->shouldProceedCallback;
    }

    /**
     * @param ?Closure $shouldProceedCallback
     */
    public function setShouldProceedCallback(?Closure $shouldProceedCallback): void
    {
        $this->shouldProceedCallback = $shouldProceedCallback;
    }

}