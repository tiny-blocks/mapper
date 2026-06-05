<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Models;

final readonly class Weekday
{
    private function __construct(private string $name)
    {
    }

    public static function fromName(string $name): Weekday
    {
        $cases = ['mon' => 'monday', 'monday' => 'monday', 'tue' => 'tuesday', 'tuesday' => 'tuesday'];

        return new Weekday(name: $cases[$name] ?? $name);
    }

    public function name(): string
    {
        return $this->name;
    }
}
