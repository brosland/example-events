<?php
declare(strict_types=1);

namespace App\Domain\Uuid;

use PascalDeVink\ShortUuid\ShortUuid as ShortUuidFactory;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class ShortUuid implements UuidInterface
{
    /**
     * @var ShortUuidFactory
     */
    private static $shortUuidFactory;
    /**
     * @var UuidInterface
     */
    private $uuid;


    public function __construct(UuidInterface $uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return $this->toString();
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $this->uuid = self::fromString($serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function compareTo(UuidInterface $other)
    {
        return $this->uuid->compareTo($other);
    }

    /**
     * {@inheritdoc}
     */
    public function equals($other)
    {
        return $this->uuid->equals($other);
    }

    /**
     * {@inheritdoc}
     */
    public function getBytes()
    {
        return $this->uuid->getBytes();
    }

    /**
     * {@inheritdoc}
     */
    public function getNumberConverter()
    {
        return $this->uuid->getNumberConverter();
    }

    /**
     * {@inheritdoc}
     */
    public function getHex()
    {
        return $this->uuid->getHex();
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldsHex()
    {
        return $this->uuid->getFieldsHex();
    }

    /**
     * {@inheritdoc}
     */
    public function getClockSeqHiAndReservedHex()
    {
        return $this->uuid->getClockSeqHiAndReservedHex();
    }

    /**
     * {@inheritdoc}
     */
    public function getClockSeqLowHex()
    {
        return $this->uuid->getClockSeqLowHex();
    }

    /**
     * {@inheritdoc}
     */
    public function getClockSequenceHex()
    {
        return $this->uuid->getClockSequenceHex();
    }

    /**
     * {@inheritdoc}
     */
    public function getDateTime()
    {
        return $this->uuid->getDateTime();
    }

    /**
     * {@inheritdoc}
     */
    public function getInteger()
    {
        return $this->uuid->getInteger();
    }

    /**
     * {@inheritdoc}
     */
    public function getLeastSignificantBitsHex()
    {
        return $this->uuid->getLeastSignificantBitsHex();
    }

    /**
     * {@inheritdoc}
     */
    public function getMostSignificantBitsHex()
    {
        return $this->uuid->getMostSignificantBitsHex();
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeHex()
    {
        return $this->uuid->getNodeHex();
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeHiAndVersionHex()
    {
        return $this->uuid->getTimeHiAndVersionHex();
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeLowHex()
    {
        return $this->uuid->getTimeLowHex();
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeMidHex()
    {
        return $this->uuid->getTimeMidHex();
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestampHex()
    {
        return $this->uuid->getTimestampHex();
    }

    /**
     * {@inheritdoc}
     */
    public function getUrn()
    {
        return $this->uuid->getUrn();
    }

    /**
     * {@inheritdoc}
     */
    public function getVariant()
    {
        return $this->uuid->getVariant();
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return $this->uuid->getVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->uuid->jsonSerialize();
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return self::getShortUuidFactory()->encode($this);
    }


    public function __toString(): string
    {
       return $this->toString();
    }

    /**
     * @param string $shortUuid
     * @return UuidInterface
     * @throws InvalidUuidStringException
     */
    public static function fromString($shortUuid)
    {
        $uuid = self::getShortUuidFactory()->decode($shortUuid);

        return new self($uuid);
    }

    public static function uuid4(): self
    {
        return new self(Uuid::uuid4());
    }

    private static function getShortUuidFactory(): ShortUuidFactory
    {
        if (self::$shortUuidFactory === null) {
            self::$shortUuidFactory = new ShortUuidFactory();
        }

        return self::$shortUuidFactory;
    }
}