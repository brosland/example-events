<?php

namespace App\Domain\Uuid;

use Ramsey\Uuid\UuidInterface;

trait UuidIdentifier
{

    /**
     * @var UuidInterface
     */
    protected $id;


    final public function getId(): UuidInterface
    {
        return $this->id;
    }
}