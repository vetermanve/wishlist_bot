<?php


namespace Verse\Scheduler\Service;


use Verse\Run\Util\Uuid;
use Verse\Scheduler\Storage\EventsStorage;
use Verse\Scheduler\Storage\TimeSlotStorage;
use Verse\Storage\StorageProto;

class TimeEventScheduler
{
    /**
     * @var ?StorageProto
     */
    private ?StorageProto $timeSlotStorage;

    /**
     * @var ?StorageProto
     */
    private ?StorageProto $eventStorage;

    public function __construct()
    {
        if (!isset($this->timeSlotStorage)) {
            $this->timeSlotStorage = new TimeSlotStorage();
        }

        if (!isset($this->eventStorage)) {
            $this->eventStorage = new EventsStorage();
        }
    }

    public function addEvent($user, $time, $route, $data, $ttl = null)
    {
        $id = Uuid::v4();
        $timeEventWriteResult = $this->eventStorage->write()->insert($id, [
            EventsStorage::ROUTE => $route,
            EventsStorage::TIME => $time,
            EventsStorage::USER => $user,
            EventsStorage::DATA => $data,
            EventsStorage::TTL => $ttl,
        ], __METHOD__);

        if ($timeEventWriteResult) {
            $timeSlotWriteRetryCount = 10;
            do {
                $ts = $this->timeSlotStorage->read()->get($time, __METHOD__);
                if (!$ts) {
                    $bind = [
                        TimeSlotStorage::EVENT_IDS => [$id]
                    ];
                    $timeSlotWriteResult = $this->timeSlotStorage->write()->insert($time, $bind, __METHOD__);
                } else {
                    $ts[TimeSlotStorage::EVENT_IDS][] = $id;
                    $bind = [
                        TimeSlotStorage::EVENT_IDS => $ts[TimeSlotStorage::EVENT_IDS]
                    ];
                    $timeSlotWriteResult = $this->timeSlotStorage->write()->update($time, $bind, __METHOD__);
                }
            } while  (!$timeSlotWriteResult && $timeSlotWriteRetryCount-- > 0);
        }

        return $timeEventWriteResult && $timeSlotWriteResult;
    }

    /**
     * @return StorageProto|null
     */
    public function getTimeSlotStorage()
    {
        return $this->timeSlotStorage;
    }

    /**
     * @param StorageProto|null $timeSlotStorage
     */
    public function setTimeSlotStorage($timeSlotStorage): void
    {
        $this->timeSlotStorage = $timeSlotStorage;
    }

    /**
     * @return StorageProto|null
     */
    public function getEventStorage()
    {
        return $this->eventStorage;
    }

    /**
     * @param StorageProto|null $eventStorage
     */
    public function setEventStorage($eventStorage): void
    {
        $this->eventStorage = $eventStorage;
    }
}