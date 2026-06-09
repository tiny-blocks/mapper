<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Unit;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Organization;
use TinyBlocks\Mapper\Configuration;
use TinyBlocks\Mapper\Mapper;
use TinyBlocks\Mapper\SnakeCase;
use TinyBlocks\Mapper\Structured;

final class StructuredTest extends TestCase
{
    public function testToArrayWhenTypeHasNoMappingThenSinglePropertyCollapses(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When an Organization with a single registration id is serialized */
        $array = $mapper->toArray(source: new Organization(registrationId: 'org-1'));

        /** @Then the single property collapses to its scalar wrapped in an array */
        self::assertSame(['org-1'], $array);
    }

    public function testToObjectWhenStructuredIsRegisteredThenRoundTripIsLossless(): void
    {
        /** @Given a mapper with a Structured mapping on Organization */
        $mapper = Mapper::create()->withMapping(type: Organization::class, mapping: Structured::create());

        /** @And an original Organization */
        $original = new Organization(registrationId: 'org-1');

        /** @When the Organization is serialized to an array and rebuilt */
        $rebuilt = $mapper->toObject(type: Organization::class, source: $mapper->toArray(source: $original));

        /** @Then the rebuilt Organization equals the original */
        self::assertEquals($original, $rebuilt);
    }

    public function testToArrayWhenStructuredIsRegisteredThenObjectShapeIsPreserved(): void
    {
        /** @Given a mapper with a Structured mapping on Organization */
        $mapper = Mapper::create()->withMapping(type: Organization::class, mapping: Structured::create());

        /** @When an Organization with a single registration id is serialized */
        $array = $mapper->toArray(source: new Organization(registrationId: 'org-1'));

        /** @Then the single property is emitted as an object instead of collapsing to a scalar */
        self::assertSame(['registrationId' => 'org-1'], $array);
    }

    public function testToArrayWhenStructuredComposesWithSnakeCaseThenKeyIsSnakeCased(): void
    {
        /** @Given a mapper with snake_case naming and a Structured mapping on Organization */
        $mapper = Mapper::create()
            ->withNaming(namingStrategy: SnakeCase::create())
            ->withMapping(type: Organization::class, mapping: Structured::create());

        /** @When an Organization with a single registration id is serialized */
        $array = $mapper->toArray(source: new Organization(registrationId: 'org-1'));

        /** @Then the preserved property key follows the active snake_case naming */
        self::assertSame(['registration_id' => 'org-1'], $array);
    }

    public function testToArrayWhenStructuredComposesWithOmittingNullsThenNullIsOmitted(): void
    {
        /** @Given a mapper with a Structured mapping on Organization */
        $mapper = Mapper::create()->withMapping(type: Organization::class, mapping: Structured::create());

        /** @When an Organization with a null registration id is serialized omitting nulls */
        $array = $mapper->toArray(
            source: new Organization(registrationId: null),
            configuration: Configuration::default()->omittingNulls()
        );

        /** @Then the null property is omitted while the object shape is preserved */
        self::assertSame([], $array);
    }
}
