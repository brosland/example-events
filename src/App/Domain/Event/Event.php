<?php
declare(strict_types=1);

namespace App\Domain\Event;

use DateTimeImmutable;
use Serializable;

abstract class Event implements Serializable
{
    /**
     * @var DateTimeImmutable
     */
    private $createdAt;


    public function __construct(DateTimeImmutable $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}