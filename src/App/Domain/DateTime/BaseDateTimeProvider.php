<?php
declare(strict_types=1);

namespace App\Domain\DateTime;

use DateTimeImmutable;
use Exception;

class BaseDateTimeProvider implements DateTimeProvider
{
    /**
     * @return DateTimeImmutable
     * @throws Exception
     */
    public function getNow(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}