<?php
declare(strict_types=1);

namespace App\Infrastructure\Event;

use App\Domain\Event\EventEntity;
use App\Domain\Event\EventNotFoundException;
use App\Domain\Event\EventRepository;
use App\Domain\Event\Message\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\UuidInterface;

final class DoctrineEventRepository implements EventRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var MessageRepository
     */
    private $messageRepository;


    public function __construct(
        EntityManagerInterface $entityManager,
        MessageRepository $messageRepository
    ) {
        $this->entityManager = $entityManager;
        $this->messageRepository = $messageRepository;
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

        foreach ($event->getMessages() as $message) {
            $this->messageRepository->add($message);
        }
    }
}