<?php
declare(strict_types=1);

namespace App\Infrastructure\Event;

use App\Domain\DateTime\DateTimeProvider;
use App\Domain\Event\Event;
use App\Domain\Event\EventEntity;
use App\Domain\Event\EventManager as EventManagerInterface;
use App\Domain\Event\EventRepository;
use App\Domain\Event\Message\MessageEntity;
use App\Domain\Event\Subscriber;
use InvalidArgumentException;
use RuntimeException;

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

            $name = md5($handleMethod);
            $this->listeners[$eventName][$name] = [$subscriber, $handleMethod];
        }
    }

    public function publish(Event $event): void
    {
        $eventName = get_class($event);
        $eventEntity = new EventEntity($event);

        if (isset($this->listeners[$eventName])) {
            foreach ($this->listeners[$eventName] as $receiver => $callback) {
                $eventEntity->addMessage($receiver);
            }
        } else {
            $eventEntity->markProcessed($this->dateTimeProvider);
        }

        $this->eventRepository->add($eventEntity);
    }

    public function deliverMessage(MessageEntity $message): void
    {
        $event = $message->getEvent()->getEvent();
        $eventName = get_class($event);

        if (!isset($this->listeners[$eventName][$message->getReceiver()])) {
            throw new RuntimeException(
                "Missing receiver '{$message->getReceiver()}' for the event '{$eventName}'."
            );
        }

        $listener = $this->listeners[$eventName][$message->getReceiver()];
        $listener($event);

        $message->markDelivered($this->dateTimeProvider);

        if (count($message->getEvent()->getUndeliveredMessages()) === 0) {
            $message->getEvent()->markProcessed($this->dateTimeProvider);
        };
    }
}