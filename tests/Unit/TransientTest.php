<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Unit;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\WithTransient;
use TinyBlocks\Mapper\Mapper;

final class TransientTest extends TestCase
{
    public function testToArrayThenTransientPropertyIsExcludedAndTheRemainingStateIsKept(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a type carrying a transient property is serialized */
        $array = $mapper->toArray(
            source: new WithTransient(name: 'alpha', amount: 7, checksum: 'secret', reference: 'ref-01')
        );

        /** @Then the transient property is absent and every other property is kept */
        self::assertSame(['name' => 'alpha', 'amount' => 7, 'reference' => 'ref-01'], $array);
    }
}
