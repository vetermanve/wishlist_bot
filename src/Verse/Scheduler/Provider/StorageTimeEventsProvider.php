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
     * @var int Shifting time for test reasons or special purpose
     */
    private int $timeShift = 0;

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
            $start = time() + $this->timeShift;
            $endingTime = $start;
            $blockSizes = [30, 300, 3000];

            foreach ($blockSizes as $blockSize) {
                $this->runBlock($endingTime, $blockSize);
                $endingTime -= $blockSize;
            }

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

        foreach ($eventsBySlots as $timeSlotRecord) {
            $events = $this->eventStorage->read()->mGet($timeSlotRecord[TimeSlotStorage::EVENT_IDS], __METHOD__);

            foreach ($events as $index => $event) {
                if ($event) {
                    $this->runEvent($event);
                    $this->eventStorage->write()->remove($event[EventsStorage::ID], __METHOD__);
                } else {
                    $this->runtime->warning('Event found in list, but not found in storage', ['event_id' => $index, 'slot' => $timeSlotRecord[TimeSlotStorage::ID],]);
                }
            }

            $this->timeSlotStorage->write()->remove($timeSlotRecord[TimeSlotStorage::ID], __METHOD__);
        }
    }

    public function getTimeRange($endTime, $size)
    {
        $start = $endTime - $this->partition;
        return range($start - $size, $start, $this->partitionsCount);
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

    /**
     * @return int
     */
    public function getTimeShift(): int
    {
        return $this->timeShift;
    }

    /**
     * @param int $timeShift
     */
    public function setTimeShift(int $timeShift): void
    {
        $this->timeShift = $timeShift;
    }

}