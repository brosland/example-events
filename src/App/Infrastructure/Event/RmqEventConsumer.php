<?php

namespace App\Infrastructure\Event;

use App\Domain\Event\EventNotFoundException;
use App\Domain\Event\EventRepository;
use App\Domain\Uuid\ShortUuid;
use App\Infrastructure\RabbitMQ\BaseConsumer;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use Tracy\Debugger;

final class RmqEventConsumer extends BaseConsumer
{
    /**
     * @var EventManager
     */
    private $eventManager;
    /**
     * @var EventRepository
     */
    private $eventRepository;


    public function __construct(
        EntityManagerInterface $entityManager,
        EventRepository $eventRepository,
        EventManager $eventManager
    ) {
        parent::__construct($entityManager);

        $this->eventManager = $eventManager;
        $this->eventRepository = $eventRepository;
    }

    public function process(AMQPMessage $message): int
    {
        try {
            $eventId = $message->getBody();
            $eventUuid = ShortUuid::fromString($eventId);
            $event = $this->eventRepository->getEventById($eventUuid);

            if (!$event->isProcessed()) {
                $this->eventManager->processEvent($event);
            }

            return BaseConsumer::MSG_ACK;
        } catch (EventNotFoundException $e) {
            Debugger::log($e);

            return BaseConsumer::MSG_REJECT;
        } catch (Exception $e) {
            Debugger::log($e);

            return BaseConsumer::MSG_REJECT_REQUEUE;
        }
    }
}