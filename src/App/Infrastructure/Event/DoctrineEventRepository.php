<?php
declare(strict_types=1);

namespace App\Infrastructure\Event;

use App\Domain\Event\EventEntity;
use App\Domain\Event\EventNotFoundException;
use App\Domain\Event\EventRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Kdyby\RabbitMq\Connection;
use Ramsey\Uuid\UuidInterface;

final class DoctrineEventRepository implements EventRepository, EventSubscriber
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var Connection
     */
    private $rabbitMqConnection;
    /**
     * @var EventEntity[]
     */
    private $newAddedEvents = [];


    public function __construct(
        EntityManagerInterface $entityManager,
        Connection $rabbitMqConnection
    ) {
        $this->entityManager = $entityManager;
        $this->rabbitMqConnection = $rabbitMqConnection;
    }

    /**
     * @throws EventNotFoundException
     */
    public function getEventById(UuidInterface $id): EventEntity
    {
        /** @var null|EventEntity $event */
        $event = $this->entityManager->find(EventEntity::class, $id);

        if ($event === null) {
            throw EventNotFoundException::notFoundById($id->toString());
        }

        return $event;
    }

    public function add(EventEntity $event): void
    {
        $this->entityManager->persist($event);

        $this->newAddedEvents[$event->getId()->toString()] = $event;
    }

    public function getSubscribedEvents(): array
    {
        return [Events::postFlush => 'sendEventsToProcess'];
    }

    public function sendEventsToProcess(PostFlushEventArgs $args): void
    {
        $producer = $this->rabbitMqConnection->getProducer('app.events');

        foreach ($this->newAddedEvents as $id => $event) {
            $producer->publish($id);
        }

        $this->newAddedEvents = [];
    }
}