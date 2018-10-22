<?php
declare(strict_types=1);

use App\Domain\Event\Message\MessageEntity;
use App\Domain\Event\Message\MessageNotFoundException;
use App\Domain\Event\Message\MessageRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Kdyby\RabbitMq\Connection;
use Ramsey\Uuid\UuidInterface;

final class DoctrineMessageRepository implements MessageRepository, EventSubscriber
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
     * @var MessageEntity[]
     */
    private $newAddedMessages = [];


    public function __construct(
        EntityManagerInterface $entityManager,
        Connection $rabbitMqConnection
    ) {
        $this->entityManager = $entityManager;
        $this->rabbitMqConnection = $rabbitMqConnection;
    }

    public function getMessageById(UuidInterface $id): MessageEntity
    {
        /** @var null|MessageEntity $message */
        $message = $this->entityManager->find(MessageEntity::class, $id);

        if ($message === null) {
            throw MessageNotFoundException::notFoundById($id->toString());
        }

        return $message;
    }

    public function add(MessageEntity $message): void
    {
        $this->entityManager->persist($message);

        $this->newAddedMessages[$message->getId()->toString()] = $message;
    }

    public function remove(MessageEntity $message): void
    {
        $this->entityManager->remove($message);

        $messageId = $message->getId()->toString();

        if (isset($this->newAddedMessages[$messageId])) {
            unset($this->newAddedMessages[$messageId]);
        }
    }

    public function getSubscribedEvents(): array
    {
        return [Events::postFlush => 'sendMessagesToProcess'];
    }

    public function sendMessagesToProcess(PostFlushEventArgs $args): void
    {
        $producer = $this->rabbitMqConnection->getProducer('app.event_messages');

        foreach ($this->newAddedMessages as $id => $message) {
            $producer->publish($id);
        }

        $this->newAddedMessages = [];
    }
}