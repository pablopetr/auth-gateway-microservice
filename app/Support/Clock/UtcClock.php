<?php

namespace App\Support\Clock;

use DateTimeImmutable;
use DateTimeZone;
use Psr\Clock\ClockInterface;

final class UtcClock implements ClockInterface
{
    /**
     * @throws \DateMalformedStringException
     */
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
