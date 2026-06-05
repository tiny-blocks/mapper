<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Receipt
{
    public function __construct(
        public Mood $mood,
        public Crate $crate,
        public Pulse $pulse,
        public Coupon $coupon,
        public Moment $moment,
        public Ticket $ticket
    ) {
    }
}
