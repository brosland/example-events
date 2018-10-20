<?php
declare(strict_types=1);

namespace App\Domain\Event;

interface Subscriber
{
    /**
     * @return string[] [EventClass => handleMethodName, ...]
     */
    function getSubscribedEvents(): array;
}