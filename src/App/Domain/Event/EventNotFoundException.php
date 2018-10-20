<?php
declare(strict_types=1);

namespace App\Domain\Event;

use RuntimeException;

final class EventNotFoundException extends RuntimeException
{
    public static function notFoundById(string $id): self
    {
        return new self("The event not found by ID '{$id}'.");
    }
}