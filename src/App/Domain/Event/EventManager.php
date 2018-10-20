<?php
declare(strict_types=1);

namespace App\Domain\Event;

interface EventManager
{
    function publish(Event $event): void;

    function addSubscriber(Subscriber $subscriber);
}