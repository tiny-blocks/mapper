<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Unit;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Address;
use TinyBlocks\Mapper\Configuration;
use TinyBlocks\Mapper\Serializable;

final class MappableTraitTest extends TestCase
{
    public function testBuildFromWhenArraySourceThenInstanceIsBuilt(): void
    {
        /** @When an Address is built from an associative array */
        $address = Address::buildFrom(source: ['street' => 'Av. Paulista', 'city' => 'São Paulo']);

        /** @Then the instance carries the source values */
        self::assertEquals(new Address(city: 'São Paulo', street: 'Av. Paulista'), $address);
    }

    public function testBuildFromWhenJsonStringSourceThenInstanceIsBuilt(): void
    {
        /** @When an Address is built from a JSON string */
        $address = Address::buildFrom(source: '{"street":"Av. Paulista","city":"São Paulo"}');

        /** @Then the instance carries the decoded values */
        self::assertEquals(new Address(city: 'São Paulo', street: 'Av. Paulista'), $address);
    }

    public function testToArrayWhenConfigurationOmitsAFieldThenItIsAbsent(): void
    {
        /** @Given an address instance */
        $address = new Address(city: 'São Paulo', street: 'Av. Paulista');

        /** @When the address is converted to an array with an omitted city */
        $array = $address->toArray(configuration: Configuration::default()->omitting('city'));

        /** @Then the omitted property is absent */
        self::assertSame(['street' => 'Av. Paulista'], $array);
    }

    public function testToArrayWhenNoConfigurationThenAllPropertiesAreEmitted(): void
    {
        /** @Given an address instance */
        $address = new Address(city: 'São Paulo', street: 'Av. Paulista');

        /** @When the address is converted to an array */
        $array = $address->toArray();

        /** @Then the array carries all properties */
        self::assertSame(['city' => 'São Paulo', 'street' => 'Av. Paulista'], $array);
    }

    public function testToJsonWhenNoConfigurationThenJsonRepresentationIsReturned(): void
    {
        /** @Given an address instance */
        $address = new Address(city: 'São Paulo', street: 'Av. Paulista');

        /** @When the address is converted to JSON */
        $json = $address->toJson();

        /** @Then the JSON carries every property */
        self::assertSame('{"city":"São Paulo","street":"Av. Paulista"}', $json);
    }

    public function testToArrayWhenTypedAsSerializableThenRespondsWithoutArguments(): void
    {
        /** @Given a value object referenced only through the Serializable contract */
        $serializable = new Address(city: 'São Paulo', street: 'Av. Paulista');

        /** @When it is serialized through the parameterless contract */
        $array = (static fn(Serializable $value): array => $value->toArray())($serializable);

        /** @Then the array carries all properties */
        self::assertSame(['city' => 'São Paulo', 'street' => 'Av. Paulista'], $array);
    }
}
