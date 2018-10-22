<?php
declare(strict_types=1);

namespace App\Domain\Event\Message;

use App\Domain\DateTime\DateTimeProvider;
use App\Domain\Event\EventEntity;
use App\Domain\Uuid\UuidIdentifier;
use DateTimeImmutable;
use RuntimeException;

class MessageEntity
{
    use UuidIdentifier;

    /**
     * @var EventEntity
     */
    private $event;
    /**
     * @var string
     */
    private $receiver;
    /**
     * @var ?DateTimeImmutable
     */
    private $deliveredAt;


    public function __construct(EventEntity $event, string $receiver)
    {
        $this->event = $event;
        $this->receiver = $receiver;
    }

    public function getEvent(): EventEntity
    {
        return $this->event;
    }

    public function getReceiver(): string
    {
        return $this->receiver;
    }

    public function isDelivered(): bool
    {
        return $this->deliveredAt !== null;
    }

    public function getDeliveredAt(): ?DateTimeImmutable
    {
        return $this->deliveredAt;
    }

    public function markDelivered(DateTimeProvider $dateTimeProvider): void
    {
        if ($this->isDelivered()) {
            throw new RuntimeException('The message has been already delivered.');
        }

        $this->deliveredAt = $dateTimeProvider->getNow();
    }
}