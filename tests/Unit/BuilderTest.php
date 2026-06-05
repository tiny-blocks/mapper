<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Unit;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Amount;
use Test\TinyBlocks\Mapper\Models\Cpf;
use Test\TinyBlocks\Mapper\Models\Currency;
use Test\TinyBlocks\Mapper\Models\Duplicate\Pix as DuplicatePix;
use Test\TinyBlocks\Mapper\Models\PaymentMethod;
use Test\TinyBlocks\Mapper\Models\Pix;
use TinyBlocks\Mapper\Exceptions\InvalidSubtypeCase;
use TinyBlocks\Mapper\Exceptions\UnexpectedKey;
use TinyBlocks\Mapper\Mapper;
use TinyBlocks\Mapper\SnakeCase;
use TinyBlocks\Mapper\Subtype;

final class BuilderTest extends TestCase
{
    public function testWithNamingThenReturnsANewInstance(): void
    {
        /** @Given a base mapper */
        $base = Mapper::create();

        /** @When the naming strategy is replaced */
        $derived = $base->withNaming(namingStrategy: SnakeCase::create());

        /** @Then a new mapper instance is returned */
        self::assertNotSame($base, $derived);
    }

    public function testWithMappingThenReturnsANewInstance(): void
    {
        /** @Given a base mapper */
        $base = Mapper::create();

        /** @When a Subtype mapping is registered */
        $derived = $base->withMapping(
            type: PaymentMethod::class,
            mapping: Subtype::by(field: 'type', types: [Pix::class])
        );

        /** @Then a new mapper instance is returned */
        self::assertNotSame($base, $derived);
    }

    public function testWithNamingThenLeavesTheOriginalUnchanged(): void
    {
        /** @Given a base mapper */
        $base = Mapper::create();

        /** @When a snake_case derived mapper is created */
        $base->withNaming(namingStrategy: SnakeCase::create());

        /** @Then the original mapper still uses identity naming */
        self::assertEquals(
            new Amount(amount: 5, currency: Currency::BRL),
            $base->toObject(type: Amount::class, source: ['amount' => 5, 'currency' => 'BRL'])
        );
    }

    public function testRejectingUnknownKeysThenReturnsANewInstance(): void
    {
        /** @Given a base mapper */
        $base = Mapper::create();

        /** @When strict mode is enabled */
        $derived = $base->rejectingUnknownKeys();

        /** @Then a new mapper instance is returned */
        self::assertNotSame($base, $derived);
    }

    public function testRejectingUnknownKeysThenLeavesTheOriginalLenient(): void
    {
        /** @Given a base mapper */
        $base = Mapper::create();

        /** @When a strict copy is created */
        $base->rejectingUnknownKeys();

        /** @Then the original mapper still ignores unknown keys */
        self::assertEquals(
            new Amount(amount: 5, currency: Currency::BRL),
            $base->toObject(type: Amount::class, source: ['amount' => 5, 'currency' => 'BRL', 'extra' => 1])
        );
    }

    public function testRejectingUnknownKeysWhenAppliedToDerivedThenStrictModeFires(): void
    {
        /** @Given a strict mapper */
        $mapper = Mapper::create()->rejectingUnknownKeys();

        /** @Then strict mode raises on unknown keys */
        $this->expectException(UnexpectedKey::class);

        /** @When an unknown key is passed */
        $mapper->toObject(type: Amount::class, source: ['amount' => 5, 'currency' => 'BRL', 'extra' => 1]);
    }

    public function testByWhenTwoTypesDeriveTheSameSubtypeValueThenInvalidSubtypeCaseIsRaised(): void
    {
        /** @Then an invalid-subtype-case exception describing the collision is raised */
        $this->expectException(InvalidSubtypeCase::class);

        /** @When a convention-based Subtype mapping is built from two types sharing a short name */
        Subtype::by(field: 'type', types: [Pix::class, DuplicatePix::class]);
    }

    public function testWithMappingWhenSubtypeCaseIsNotASubtypeOfRegisteredTypeThenInvalidSubtypeCaseIsRaised(): void
    {
        /** @Given a base mapper */
        $mapper = Mapper::create();

        /** @Then an invalid-subtype-case exception describing the foreign case is raised */
        $this->expectException(InvalidSubtypeCase::class);

        /** @When a Subtype whose case belongs to another family is registered for PaymentMethod */
        $mapper->withMapping(
            type: PaymentMethod::class,
            mapping: Subtype::by(field: 'type', types: [Cpf::class])
        );
    }
}
