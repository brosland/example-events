<?php
declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\DateTime\DateTimeProvider;
use App\Domain\Uuid\UuidIdentifier;
use DateTimeImmutable;
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


    public function __construct(Event $event)
    {
        $this->createdAt = $event->getCreatedAt();
        $this->type = get_class($event);
        $this->event = serialize($event);
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