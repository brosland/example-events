<?php
declare(strict_types=1);

namespace App\Infrastructure\Event;

use App\Domain\DateTime\DateTimeProvider;
use App\Domain\Event\Event;
use App\Domain\Event\EventEntity;
use App\Domain\Event\EventManager as EventManagerInterface;
use App\Domain\Event\EventRepository;
use App\Domain\Event\Subscriber;
use InvalidArgumentException;

final class EventManager implements EventManagerInterface
{
    /**
     * @var array
     */
    private $listeners = [];
    /**
     * @var EventRepository
     */
    private $eventRepository;
    /**
     * @var DateTimeProvider
     */
    private $dateTimeProvider;


    public function __construct(
        EventRepository $eventRepository,
        DateTimeProvider $dateTimeProvider
    ) {
        $this->eventRepository = $eventRepository;
        $this->dateTimeProvider = $dateTimeProvider;
    }

    public function addSubscriber(Subscriber $subscriber): void
    {
        foreach ($subscriber->getSubscribedEvents() as $eventName => $handleMethod) {
            if (!class_exists($eventName)) {
                throw new InvalidArgumentException("Event class '{$eventName}' not found.");
            }

            if (!method_exists($subscriber, $handleMethod)) {
                $error = sprintf("The subscriber '%s' does not have method '%s'.",
                    get_class($subscriber), $handleMethod
                );

                throw new InvalidArgumentException($error);
            }

            $this->listeners[$eventName][] = [$subscriber, $handleMethod];
        }
    }

    public function publish(Event $event): void
    {
        $eventEntity = new EventEntity($event);

        $this->eventRepository->add($eventEntity);
    }

    public function processEvent(EventEntity $eventEntity): void
    {
        $event = $eventEntity->getEvent();
        $eventName = $eventEntity->getType();

        if (isset($this->listeners[$eventName])) {
            foreach ($this->listeners[$eventName] as $listener) {
                $listener($event);
            }
        }

        $eventEntity->markProcessed($this->dateTimeProvider);
    }
}