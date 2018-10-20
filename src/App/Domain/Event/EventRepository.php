<?php
declare(strict_types=1);

namespace App\Domain\Event;

use Ramsey\Uuid\UuidInterface;

interface EventRepository
{
    /**
     * @throws EventNotFoundException
     */
    function getEventById(UuidInterface $id): EventEntity;

    function add(EventEntity $event): void;
}