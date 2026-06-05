<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Amount;
use Test\TinyBlocks\Mapper\Models\Charge;
use Test\TinyBlocks\Mapper\Models\Cnpj;
use Test\TinyBlocks\Mapper\Models\Cpf;
use Test\TinyBlocks\Mapper\Models\Currency;
use Test\TinyBlocks\Mapper\Models\DebitCard;
use Test\TinyBlocks\Mapper\Models\Money;
use Test\TinyBlocks\Mapper\Models\Order;
use Test\TinyBlocks\Mapper\Models\Payment;
use Test\TinyBlocks\Mapper\Models\PaymentMethod;
use Test\TinyBlocks\Mapper\Models\Pix;
use Test\TinyBlocks\Mapper\Models\Positive;
use Test\TinyBlocks\Mapper\Models\Profile;
use Test\TinyBlocks\Mapper\Models\Refund;
use Test\TinyBlocks\Mapper\Models\Refunds;
use Test\TinyBlocks\Mapper\Models\Severity;
use Test\TinyBlocks\Mapper\Models\TaxId;
use TinyBlocks\Mapper\JsonColumn;
use TinyBlocks\Mapper\Layout;
use TinyBlocks\Mapper\Mapper;
use TinyBlocks\Mapper\SnakeCase;
use TinyBlocks\Mapper\Subtype;

final class RoundTripTest extends TestCase
{
    public function testToObjectAndToArrayWhenAggregateGraphThenRoundTripIsLossless(): void
    {
        /** @Given a mapper configured for the Payment aggregate */
        $mapper = Mapper::create()
            ->withNaming(namingStrategy: SnakeCase::create())
            ->withMapping(
                type: PaymentMethod::class,
                mapping: Subtype::by(field: 'type', types: [Pix::class, DebitCard::class])
            )
            ->withMapping(
                type: Payment::class,
                mapping: Layout::from(paths: [
                    'order.amount.amount'  => 'order_amount_value',
                    'charge.amount.amount' => 'charge_amount_value',
                    'charge.paymentMethod' => new JsonColumn(column: 'payment_method'),
                    'charge.refunds'       => new JsonColumn(column: 'refunds')
                ])
            );

        /** @And an original aggregate */
        $original = new Payment(
            order: new Order(amount: new Amount(amount: 5000, currency: Currency::BRL)),
            charge: new Charge(
                amount: new Amount(amount: 5000, currency: Currency::BRL),
                refunds: Refunds::createFrom(elements: [
                    new Refund(amount: new Amount(amount: 100, currency: Currency::BRL), reference: 'r-1'),
                    new Refund(amount: new Amount(amount: 200, currency: Currency::BRL), reference: 'r-2')
                ]),
                paymentMethod: new Pix(payerId: 'alice')
            )
        );

        /** @When the aggregate is serialized to a row and rebuilt */
        $rebuilt = $mapper->toObject(type: Payment::class, source: $mapper->toArray(source: $original));

        /** @Then the rebuilt aggregate equals the original */
        self::assertEquals($original, $rebuilt);
    }

    public function testToObjectAndToArrayWhenPureEnumPropertyThenRoundTripIsLossless(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @And a Profile carrying a pure enum severity */
        $original = new Profile(
            name: 'Gustavo',
            title: 'Engineer',
            severity: Severity::HIGH,
            createdAt: new DateTimeImmutable('2024-01-02T03:04:05+00:00')
        );

        /** @When the Profile is serialized to an array and rebuilt */
        $rebuilt = $mapper->toObject(type: Profile::class, source: $mapper->toArray(source: $original));

        /** @Then the rebuilt Profile equals the original, preserving the pure enum case by name */
        self::assertEquals($original, $rebuilt);
    }

    public function testToObjectAndToArrayWhenValueObjectHasPrivatePropertiesThenRoundTripIsLossless(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @And a Money value object with a private constructor and private properties */
        $original = Money::of(amount: Positive::from(value: 50), currency: Currency::BRL);

        /** @When the value object is serialized to an array and rebuilt */
        $rebuilt = $mapper->toObject(type: Money::class, source: $mapper->toArray(source: $original));

        /** @Then the rebuilt value object equals the original */
        self::assertEquals($original, $rebuilt);
    }

    public function testToObjectAndToArrayWhenSubtypeConcreteHasPrivatePropertiesThenRoundTripIsLossless(): void
    {
        /** @Given a mapper with a Subtype mapping on TaxId */
        $mapper = Mapper::create()->withMapping(
            type: TaxId::class,
            mapping: Subtype::by(field: 'type', types: [Cpf::class, Cnpj::class])
        );

        /** @And a Cpf whose single private property forms its body */
        $original = Cpf::from(digits: '12345678901');

        /** @When the concrete subtype is serialized and rebuilt through the abstract base */
        $rebuilt = $mapper->toObject(type: TaxId::class, source: $mapper->toArray(source: $original));

        /** @Then the rebuilt subtype equals the original */
        self::assertEquals($original, $rebuilt);
    }
}
