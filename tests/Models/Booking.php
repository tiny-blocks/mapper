<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

use TinyBlocks\Time\Instant;
use TinyBlocks\Time\LocalDate;

final readonly class Booking
{
    public function __construct(public LocalDate $stayDate, public Instant $confirmedAt)
    {
    }
}
