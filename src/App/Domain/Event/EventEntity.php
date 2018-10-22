<?php
declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\DateTime\DateTimeProvider;
use App\Domain\Event\Message\MessageEntity;
use App\Domain\Uuid\UuidIdentifier;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use RuntimeException;

class EventEntity
{
    use UuidIdentifier;

    /**
     * @var DateTimeImmutable
     */
    private $createdAt;
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $event;
    /**
     * @var null|DateTimeImmutable
     */
    private $processedAt;
    /**
     * @var Collection
     */
    private $messages;


    public function __construct(Event $event)
    {
        $this->createdAt = $event->getCreatedAt();
        $this->type = get_class($event);
        $this->event = serialize($event);
        $this->messages = new ArrayCollection();
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEvent(): Event
    {
        return unserialize($this->event);
    }

    public function addMessage(string $receiver): void
    {
        if ($this->messages->containsKey($receiver)) {
            throw new RuntimeException(
                "Duplicate message for the receiver '{$receiver}'."
            );
        }

        $this->messages[$receiver] = new MessageEntity($this, $receiver);
    }

    /**
     * @return MessageEntity[]
     */
    public function getMessages(): array
    {
        return $this->messages->toArray();
    }

    /**
     * @return MessageEntity[]
     */
    public function getUndeliveredMessages(): array
    {
        return array_filter(
            $this->getMessages(),
            function (MessageEntity $message) {
                return !$message->isDelivered();
            }
        );
    }

    public function isProcessed(): bool
    {
        return $this->processedAt !== null;
    }

    public function getProcessedAt(): ?DateTimeImmutable
    {
        return $this->processedAt;
    }

    public function markProcessed(DateTimeProvider $dateTimeProvider): void
    {
        if ($this->isProcessed()) {
            throw new RuntimeException('The event has been already processed.');
        }

        $this->processedAt = $dateTimeProvider->getNow();
    }
}