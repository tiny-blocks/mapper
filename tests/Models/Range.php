<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Range
{
    private function __construct(private int $lowerBound, private int $upperBound)
    {
    }

    public static function of(int $lowerBound, int $upperBound): Range
    {
        return $lowerBound <= $upperBound
            ? new Range(lowerBound: $lowerBound, upperBound: $upperBound)
            : new Range(lowerBound: $upperBound, upperBound: $lowerBound);
    }

    public function lowerBound(): int
    {
        return $this->lowerBound;
    }

    public function upperBound(): int
    {
        return $this->upperBound;
    }
}
