<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Unit;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Amount;
use Test\TinyBlocks\Mapper\Models\Currency;
use TinyBlocks\Mapper\Deserializer;
use TinyBlocks\Mapper\Mapper;
use TinyBlocks\Mapper\Serializer;

final class ServiceContractTest extends TestCase
{
    public function testToObjectWhenMapperIsConsumedThroughTheDeserializerContractThenItBuilds(): void
    {
        /** @Given a consumer that depends only on the Deserializer contract */
        $build = static fn(Deserializer $deserializer): Amount => $deserializer->toObject(
            type: Amount::class,
            source: ['amount' => 50, 'currency' => 'BRL']
        );

        /** @When the mapper is supplied as the Deserializer */
        $amount = $build(Mapper::create());

        /** @Then the consumer receives the built instance */
        self::assertEquals(new Amount(amount: 50, currency: Currency::BRL), $amount);
    }

    public function testToArrayWhenMapperIsConsumedThroughTheSerializerContractThenItSerializes(): void
    {
        /** @Given a consumer that depends only on the Serializer contract */
        $serialize = static fn(Serializer $serializer): array => $serializer->toArray(
            source: new Amount(amount: 50, currency: Currency::BRL)
        );

        /** @When the mapper is supplied as the Serializer */
        $array = $serialize(Mapper::create());

        /** @Then the consumer receives the serialized array */
        self::assertSame(['amount' => 50, 'currency' => 'BRL'], $array);
    }
}
