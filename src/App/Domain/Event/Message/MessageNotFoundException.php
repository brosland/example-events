<?php
declare(strict_types=1);

namespace App\Domain\Event\Message;

use RuntimeException;

final class MessageNotFoundException extends RuntimeException
{
    public static function notFoundById(string $id): self
    {
        return new self("The message not found by ID '{$id}'.");
    }
}