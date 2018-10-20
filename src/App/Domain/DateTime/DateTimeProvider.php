<?php
declare(strict_types=1);

namespace App\Domain\DateTime;

use DateTimeImmutable;

interface DateTimeProvider
{
    function getNow(): DateTimeImmutable;
}