<?php
declare(strict_types=1);

namespace App\Domain\Event;

interface EventManager
{
    function addSubscriber(Subscriber $subscriber): void;

    function publish(Event $event): void;
}