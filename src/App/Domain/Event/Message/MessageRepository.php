<?php
declare(strict_types=1);

namespace App\Domain\Event\Message;

use Ramsey\Uuid\UuidInterface;

interface MessageRepository
{
    /**
     * @throws MessageNotFoundException
     */
    function getMessageById(UuidInterface $id): MessageEntity;

    function add(MessageEntity $message): void;

    function remove(MessageEntity $message): void;
}