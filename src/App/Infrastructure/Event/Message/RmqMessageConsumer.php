<?php

namespace App\Infrastructure\Event\Message;

use App\Domain\Event\Message\MessageNotFoundException;
use App\Domain\Event\Message\MessageRepository;
use App\Domain\Uuid\ShortUuid;
use App\Infrastructure\Event\EventManager;
use App\Infrastructure\RabbitMQ\BaseConsumer;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Tracy\Debugger;

final class RmqMessageConsumer extends BaseConsumer
{
    /**
     * @var EventManager
     */
    private $eventManager;
    /**
     * @var MessageRepository
     */
    private $messageRepository;


    public function __construct(
        EntityManagerInterface $entityManager,
        EventManager $eventManager,
        MessageRepository $messageRepository
    ) {
        parent::__construct($entityManager);

        $this->eventManager = $eventManager;
        $this->messageRepository = $messageRepository;
    }

    public function process(AMQPMessage $amqpMessage): int
    {
        try {
            $messageUuid = ShortUuid::fromString($amqpMessage->getBody());
            $messageEntity = $this->messageRepository->getMessageById($messageUuid);

            if (!$messageEntity->isDelivered()) {
                $this->eventManager->deliverMessage($messageEntity);
            }

            return BaseConsumer::MSG_ACK;
        } catch (InvalidUuidStringException | MessageNotFoundException $e) {
            Debugger::log($e);

            return BaseConsumer::MSG_REJECT;
        } catch (Exception $e) {
            Debugger::log($e);

            return BaseConsumer::MSG_REJECT_REQUEUE;
        }
    }
}