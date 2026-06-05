<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Unit;

use ArrayIterator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Amount;
use Test\TinyBlocks\Mapper\Models\Charge;
use Test\TinyBlocks\Mapper\Models\Currency;
use Test\TinyBlocks\Mapper\Models\DebitCard;
use Test\TinyBlocks\Mapper\Models\MemberId;
use Test\TinyBlocks\Mapper\Models\Order;
use Test\TinyBlocks\Mapper\Models\Owner;
use Test\TinyBlocks\Mapper\Models\Payment;
use Test\TinyBlocks\Mapper\Models\PaymentMethod;
use Test\TinyBlocks\Mapper\Models\Pix;
use Test\TinyBlocks\Mapper\Models\Profile;
use Test\TinyBlocks\Mapper\Models\Camera;
use Test\TinyBlocks\Mapper\Models\Refund;
use Test\TinyBlocks\Mapper\Models\Refunds;
use Test\TinyBlocks\Mapper\Models\Scalars;
use Test\TinyBlocks\Mapper\Models\Severity;
use Test\TinyBlocks\Mapper\Models\Studio;
use Test\TinyBlocks\Mapper\Models\Tracker;
use Test\TinyBlocks\Mapper\Models\Variant;
use Test\TinyBlocks\Mapper\Models\VersionedTag;
use Test\TinyBlocks\Mapper\Models\Wallet;
use DateTime;
use TinyBlocks\Mapper\Exceptions\UnexpectedKey;
use TinyBlocks\Mapper\Exceptions\UnknownSubtype;
use TinyBlocks\Mapper\Exceptions\UnmappableSource;
use TinyBlocks\Mapper\JsonColumn;
use TinyBlocks\Mapper\Layout;
use TinyBlocks\Mapper\Mapper;
use TinyBlocks\Mapper\Mapping;
use TinyBlocks\Mapper\MappingContext;
use TinyBlocks\Mapper\SnakeCase;
use TinyBlocks\Mapper\Subtype;

final class HydrationTest extends TestCase
{
    public function testToObjectOrNullWhenSourceIsNullThenReturnsNull(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When toObjectOrNull is called with a null source */
        $result = $mapper->toObjectOrNull(type: Amount::class, source: null);

        /** @Then the result is null */
        self::assertNull($result);
    }

    public function testToObjectWhenJsonStringSourceThenObjectIsBuilt(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When an Amount is hydrated from a JSON string */
        $amount = $mapper->toObject(type: Amount::class, source: '{"amount":99,"currency":"BRL"}');

        /** @Then the amount matches the decoded payload */
        self::assertEquals(new Amount(amount: 99, currency: Currency::BRL), $amount);
    }

    public function testToObjectWhenSourceHasIsoDateThenDateTimeIsBuilt(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a Profile is hydrated with an ISO-8601 date */
        $profile = $mapper->toObject(type: Profile::class, source: [
            'name'      => 'Gustavo',
            'title'     => 'Engineer',
            'createdAt' => '2024-01-02T03:04:05+00:00',
            'severity'  => 'LOW'
        ]);

        /** @Then the createdAt holds the corresponding DateTimeImmutable */
        self::assertEquals(new DateTimeImmutable('2024-01-02T03:04:05+00:00'), $profile->createdAt);
    }

    public function testToObjectOrNullWhenSourceIsArrayThenReturnsObject(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When toObjectOrNull is called with an array source */
        $result = $mapper->toObjectOrNull(type: Amount::class, source: ['amount' => 1, 'currency' => 'USD']);

        /** @Then the result is the corresponding object */
        self::assertEquals(new Amount(amount: 1, currency: Currency::USD), $result);
    }

    public function testToObjectWhenSourceIsArrayIteratorThenObjectIsBuilt(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When an Amount is hydrated from an ArrayIterator source */
        $amount = $mapper->toObject(
            type: Amount::class,
            source: new ArrayIterator(['amount' => 7, 'currency' => 'USD'])
        );

        /** @Then the iterator entries populate the properties */
        self::assertEquals(new Amount(amount: 7, currency: Currency::USD), $amount);
    }

    public function testToObjectWhenSourceIsNullableNullThenPropertyIsNull(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a Profile is hydrated with null title */
        $profile = $mapper->toObject(type: Profile::class, source: [
            'name'      => 'Gustavo',
            'title'     => null,
            'createdAt' => '2024-01-02T03:04:05+00:00',
            'severity'  => 'LOW'
        ]);

        /** @Then the title is null */
        self::assertNull($profile->title);
    }

    public function testToObjectWhenDateTimeTargetThenMutableInstanceIsBuilt(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a mutable DateTime is hydrated from a string */
        $instance = $mapper->toObject(type: DateTime::class, source: ['date' => '2024-01-02T03:04:05+00:00']);

        /** @Then a DateTime instance is returned */
        self::assertInstanceOf(DateTime::class, $instance);
    }

    public function testToObjectWhenLayoutColumnIsAbsentFromRowThenLeafIsNull(): void
    {
        /** @Given a mapper with a Layout on Profile */
        $mapper = Mapper::create()->withMapping(type: Profile::class, mapping: Layout::from(paths: []));

        /** @When a Profile is hydrated from a row missing the optional title column */
        $profile = $mapper->toObject(type: Profile::class, source: [
            'name'      => 'Gustavo',
            'createdAt' => '2024-01-02T03:04:05+00:00',
            'severity'  => 'LOW'
        ]);

        /** @Then the absent column is read as null */
        self::assertNull($profile->title);
    }

    public function testToObjectWhenSourceHasBackedEnumThenEnumCaseIsResolved(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When an Amount is hydrated with a backed-enum currency */
        $amount = $mapper->toObject(type: Amount::class, source: ['amount' => 1000, 'currency' => 'USD']);

        /** @Then the currency is the matching enum case */
        self::assertSame(Currency::USD, $amount->currency);
    }

    public function testToObjectWhenSubtypeFieldMatchesCaseThenConcreteIsBuilt(): void
    {
        /** @Given a mapper with a Subtype mapping on PaymentMethod */
        $mapper = Mapper::create()->withMapping(
            type: PaymentMethod::class,
            mapping: Subtype::by(field: 'type', types: [Pix::class, DebitCard::class])
        );

        /** @When a Pix source is hydrated through the abstract base */
        $method = $mapper->toObject(type: PaymentMethod::class, source: ['type' => 'pix', 'payerId' => 'alice']);

        /** @Then the concrete Pix is returned */
        self::assertEquals(new Pix(payerId: 'alice'), $method);
    }

    public function testToObjectWhenElementTypedCollectionThenCollectionIsBuilt(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a list of refund sources is hydrated as Refunds */
        $refunds = $mapper->toObject(
            type: Refunds::class,
            source: [
                ['reference' => 'r-1', 'amount' => ['amount' => 100, 'currency' => 'BRL']],
                ['reference' => 'r-2', 'amount' => ['amount' => 200, 'currency' => 'BRL']]
            ]
        );

        /** @Then the collection contains the matching refunds */
        self::assertEquals(
            Refunds::createFrom(elements: [
                new Refund(amount: new Amount(amount: 100, currency: Currency::BRL), reference: 'r-1'),
                new Refund(amount: new Amount(amount: 200, currency: Currency::BRL), reference: 'r-2')
            ]),
            $refunds
        );
    }

    public function testToObjectWhenSourceHasPureEnumNameThenEnumCaseIsResolved(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a Profile is hydrated with a pure-enum severity name */
        $profile = $mapper->toObject(type: Profile::class, source: [
            'name'      => 'Gustavo',
            'title'     => null,
            'createdAt' => '2024-01-02T03:04:05+00:00',
            'severity'  => 'HIGH'
        ]);

        /** @Then the severity matches the enum case */
        self::assertSame(Severity::HIGH, $profile->severity);
    }

    public function testToObjectWhenDateTimeSourceIsTimestampThenInstanceIsBuilt(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a Profile is hydrated with a numeric createdAt */
        $profile = $mapper->toObject(type: Profile::class, source: [
            'name'      => 'Gustavo',
            'title'     => null,
            'createdAt' => 1704164645,
            'severity'  => 'LOW'
        ]);

        /** @Then the DateTimeImmutable matches the timestamp */
        self::assertSame(1704164645, $profile->createdAt->getTimestamp());
    }

    public function testToObjectWhenScalarSourceTypesNeedCoercionThenValuesAreCast(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a Scalars is hydrated from cross-typed sources */
        $scalars = $mapper->toObject(
            type: Scalars::class,
            source: ['count' => '42', 'ratio' => '1.5', 'label' => 7, 'active' => 1]
        );

        /** @Then each property carries the cast value */
        self::assertEquals(new Scalars(count: 42, label: '7', ratio: 1.5, active: true), $scalars);
    }

    public function testToObjectWhenFlatRowAndLayoutRegisteredThenNestedGraphIsBuilt(): void
    {
        /** @Given a mapper with snake_case naming, a Subtype mapping, and a Layout mapping */
        $mapper = Mapper::create()
            ->withNaming(namingStrategy: SnakeCase::create())
            ->withMapping(
                type: PaymentMethod::class,
                mapping: Subtype::by(field: 'type', types: [Pix::class])
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

        /** @When a flat row is hydrated through the Layout */
        $payment = $mapper->toObject(type: Payment::class, source: [
            'order_amount_value'     => 1000,
            'order_amount_currency'  => 'BRL',
            'charge_amount_value'    => 1000,
            'charge_amount_currency' => 'BRL',
            'payment_method'         => '{"type":"pix","payer_id":"alice"}',
            'refunds'                => '[{"reference":"r-1","amount":{"amount":100,"currency":"BRL"}}]'
        ]);

        /** @Then the nested graph matches the expected aggregate */
        self::assertEquals(
            new Payment(
                order: new Order(amount: new Amount(amount: 1000, currency: Currency::BRL)),
                charge: new Charge(
                    amount: new Amount(amount: 1000, currency: Currency::BRL),
                    refunds: Refunds::createFrom(elements: [
                        new Refund(amount: new Amount(amount: 100, currency: Currency::BRL), reference: 'r-1')
                    ]),
                    paymentMethod: new Pix(payerId: 'alice')
                )
            ),
            $payment
        );
    }

    public function testToObjectWhenLayoutHasUnionTypedPropertyThenItIsTreatedAsLeaf(): void
    {
        /** @Given a mapper with a Layout on a class holding a union-typed property */
        $mapper = Mapper::create()->withMapping(type: Variant::class, mapping: Layout::from(paths: []));

        /** @When a Variant is hydrated from a row containing the union value */
        $variant = $mapper->toObject(type: Variant::class, source: ['value' => 'free-text']);

        /** @Then the union-typed property carries the source value directly */
        self::assertSame('free-text', $variant->value);
    }

    public function testToObjectWhenPropertyHasUnionTypeThenSourceValueIsAssignedAsIs(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a Variant with a string|int property is hydrated */
        $variant = $mapper->toObject(type: Variant::class, source: ['value' => 42]);

        /** @Then the union-typed property carries the source value directly */
        self::assertSame(42, $variant->value);
    }

    public function testToObjectWhenSourceJsonIsMalformedThenUnmappableSourceIsRaised(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @Then an unmappable-source exception describing the decode failure is raised */
        $this->expectException(UnmappableSource::class);
        $this->expectExceptionMessage('Cannot decode JSON source:');

        /** @When a malformed JSON string is given as source */
        $mapper->toObject(type: Amount::class, source: '{not json');
    }

    public function testToObjectWhenSubtypeByHasNoMatchAndHasDefaultThenDefaultIsBuilt(): void
    {
        /** @Given a mapper with a convention-based Subtype mapping and a default factory */
        $mapper = Mapper::create()->withMapping(
            type: PaymentMethod::class,
            mapping: Subtype::by(
                field: 'type',
                types: [Pix::class],
                default: static fn(): Pix => Pix::pending()
            )
        );

        /** @When an unknown subtype source is hydrated */
        $method = $mapper->toObject(type: PaymentMethod::class, source: ['type' => 'unknown']);

        /** @Then the default factory result is returned */
        self::assertEquals(Pix::pending(), $method);
    }

    public function testToObjectWhenSubtypeFieldIsAbsentAndHasDefaultThenDefaultIsBuilt(): void
    {
        /** @Given a mapper with a Subtype mapping and a default factory */
        $mapper = Mapper::create()->withMapping(
            type: PaymentMethod::class,
            mapping: Subtype::by(
                field: 'type',
                types: [Pix::class],
                default: static fn(): Pix => Pix::pending()
            )
        );

        /** @When a source missing the discriminator field is hydrated */
        $method = $mapper->toObject(type: PaymentMethod::class, source: ['payerId' => 'alice']);

        /** @Then the default factory result is returned */
        self::assertEquals(Pix::pending(), $method);
    }

    public function testToObjectWhenSourceHasUnknownKeyAndMapperIsLenientThenKeyIsIgnored(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When an Amount is hydrated with an extra unknown key */
        $amount = $mapper->toObject(
            type: Amount::class,
            source: ['amount' => 50, 'currency' => 'BRL', 'extra' => 'ignored']
        );

        /** @Then the known properties are populated and the extra is ignored */
        self::assertEquals(new Amount(amount: 50, currency: Currency::BRL), $amount);
    }

    public function testToObjectWhenSourceCarriesAnInstanceOfTheTargetTypeThenItIsUsedAsIs(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When an Owner is hydrated with an already-built MemberId in the source */
        $owner = $mapper->toObject(
            type: Owner::class,
            source: ['memberId' => new MemberId(value: 'm-1'), 'name' => 'Alice']
        );

        /** @Then the existing instance is reused without reconstruction */
        self::assertEquals(new Owner(name: 'Alice', memberId: new MemberId(value: 'm-1')), $owner);
    }

    public function testToObjectWhenSourceJsonDecodesToNonArrayThenUnmappableSourceIsRaised(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @Then an unmappable-source exception is raised */
        $this->expectException(UnmappableSource::class);

        /** @When a JSON string that decodes to a scalar is given as source */
        $mapper->toObject(type: Amount::class, source: '"not an array"');
    }

    public function testToObjectWhenSubtypeHasNoMatchAndNoDefaultThenUnknownSubtypeIsRaised(): void
    {
        /** @Given a mapper with a Subtype mapping without default */
        $mapper = Mapper::create()->withMapping(
            type: PaymentMethod::class,
            mapping: Subtype::by(field: 'type', types: [Pix::class])
        );

        /** @Then an unknown-subtype exception is raised */
        $this->expectException(UnknownSubtype::class);

        /** @When an unknown subtype source is hydrated */
        $mapper->toObject(type: PaymentMethod::class, source: ['type' => 'wallet']);
    }

    public function testToObjectWhenLayoutDerivesSnakeCaseColumnsThenLeavesAreReadByDerivation(): void
    {
        /** @Given a mapper with snake_case naming and a Layout on Studio */
        $mapper = Mapper::create()
            ->withNaming(namingStrategy: SnakeCase::create())
            ->withMapping(type: Studio::class, mapping: Layout::from(paths: []));

        /** @When a Studio is hydrated from a flat row using only derived columns */
        $studio = $mapper->toObject(type: Studio::class, source: [
            'main_camera_serial_number' => 'sn-1',
            'main_camera_shot_count'    => 7,
            'tag'                       => 'studio-a'
        ]);

        /** @Then the nested Camera columns are read by snake_case derivation */
        self::assertEquals(
            new Studio(tag: 'studio-a', mainCamera: new Camera(shotCount: 7, serialNumber: 'sn-1')),
            $studio
        );
    }

    public function testToObjectWhenCamelCasePropertyAndSnakeCaseNamingThenSourceKeyIsConverted(): void
    {
        /** @Given a mapper with snake_case naming */
        $mapper = Mapper::create()->withNaming(namingStrategy: SnakeCase::create());

        /** @When a Camera is hydrated from a snake_case source */
        $camera = $mapper->toObject(
            type: Camera::class,
            source: ['serial_number' => 'sn-1', 'shot_count' => 50]
        );

        /** @Then the camera carries the source values */
        self::assertEquals(new Camera(shotCount: 50, serialNumber: 'sn-1'), $camera);
    }

    public function testToObjectWhenNestedPropertyIsScalarForSingleValueObjectThenObjectIsBuilt(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When an Owner is hydrated with a scalar memberId */
        $owner = $mapper->toObject(type: Owner::class, source: ['memberId' => 'm-1', 'name' => 'Alice']);

        /** @Then the nested MemberId carries the scalar */
        self::assertEquals(new Owner(name: 'Alice', memberId: new MemberId(value: 'm-1')), $owner);
    }

    public function testToObjectWhenParentHasPrivatePropertyAfterAnInheritedOneThenItIsHydrated(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a subclass whose parent declares a private property is hydrated */
        $tagged = $mapper->toObject(
            type: VersionedTag::class,
            source: ['label' => 'release', 'traceId' => 't-1', 'version' => 7]
        );

        /** @Then the inherited private property is populated from the source */
        self::assertSame('t-1', $tagged->traceId());
    }

    public function testToObjectWhenPureEnumNameDoesNotMatchAnyCaseThenUnmappableSourceIsRaised(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @Then an unmappable-source exception is raised */
        $this->expectException(UnmappableSource::class);

        /** @When a Profile is hydrated with an unknown severity name */
        $mapper->toObject(type: Profile::class, source: [
            'name'      => 'Gustavo',
            'title'     => null,
            'createdAt' => '2024-01-02T03:04:05+00:00',
            'severity'  => 'NONEXISTENT'
        ]);
    }

    public function testToObjectWhenLayoutJsonColumnHasNonStringValueThenRawValueIsPassedThrough(): void
    {
        /** @Given a mapper with a Layout that maps a column as a JSON document */
        $mapper = Mapper::create()->withMapping(
            type: Owner::class,
            mapping: Layout::from(paths: ['memberId' => new JsonColumn(column: 'member')])
        );

        /** @When the JSON-marked column already carries the decoded structure rather than a JSON string */
        $owner = $mapper->toObject(
            type: Owner::class,
            source: ['member' => ['value' => 'm-1'], 'name' => 'Alice']
        );

        /** @Then the column is passed through without re-decoding */
        self::assertEquals(new Owner(name: 'Alice', memberId: new MemberId(value: 'm-1')), $owner);
    }

    public function testToObjectWhenSubtypeByDefaultNamingThenConcreteIsSelectedBySnakeCaseValue(): void
    {
        /** @Given a mapper with a convention-based Subtype mapping using default naming */
        $mapper = Mapper::create()->withMapping(
            type: PaymentMethod::class,
            mapping: Subtype::by(field: 'type', types: [Pix::class, DebitCard::class])
        );

        /** @When a source carrying the snake_case discriminator is hydrated */
        $method = $mapper->toObject(
            type: PaymentMethod::class,
            source: ['type' => 'debit_card', 'cardNumber' => '4242']
        );

        /** @Then the concrete DebitCard is returned */
        self::assertEquals(new DebitCard(cardNumber: '4242'), $method);
    }

    public function testToObjectWhenSourceHasUnknownKeyAndMapperIsStrictThenUnexpectedKeyIsRaised(): void
    {
        /** @Given a strict mapper rejecting unknown source keys */
        $mapper = Mapper::create()->rejectingUnknownKeys();

        /** @Then an unexpected-key exception is raised */
        $this->expectException(UnexpectedKey::class);

        /** @When an Amount is hydrated with an unknown key */
        $mapper->toObject(type: Amount::class, source: ['amount' => 50, 'currency' => 'BRL', 'extra' => 'no']);
    }

    public function testToObjectWhenSubtypeFieldIsNonStringAndNoDefaultThenUnknownSubtypeIsRaised(): void
    {
        /** @Given a mapper with a Subtype mapping without default */
        $mapper = Mapper::create()->withMapping(
            type: PaymentMethod::class,
            mapping: Subtype::by(field: 'type', types: [Pix::class])
        );

        /** @Then an unknown-subtype exception is raised */
        $this->expectException(UnknownSubtype::class);

        /** @When a source with a non-string discriminator value is hydrated */
        $mapper->toObject(type: PaymentMethod::class, source: ['type' => 42]);
    }

    public function testToObjectWhenScalarSourceFeedsMultiPropertyClassThenUnmappableSourceIsRaised(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @Then an unmappable-source exception is raised */
        $this->expectException(UnmappableSource::class);

        /** @When an Order is hydrated with a scalar value for its Amount property */
        $mapper->toObject(type: Order::class, source: ['amount' => 'just-a-string']);
    }

    public function testToObjectWhenDateTimePropertyReceivesNonScalarValueThenUnmappableSourceIsRaised(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @Then an unmappable-source exception is raised */
        $this->expectException(UnmappableSource::class);

        /** @When a Profile is hydrated with an array value for the DateTime property */
        $mapper->toObject(type: Profile::class, source: [
            'name'      => 'Gustavo',
            'title'     => null,
            'createdAt' => ['not' => 'a date'],
            'severity'  => 'LOW'
        ]);
    }

    public function testToObjectWhenMappableContainsTypeWithRegisteredMappingThenNestedMappingIsApplied(): void
    {
        /** @Given a custom Mapping registered for the nested Amount type */
        $amountMapping = new class () implements Mapping {
            public function read(mixed $source, MappingContext $context): object
            {
                assert(is_array($source));

                return new Amount(amount: $source['cents'], currency: Currency::BRL);
            }

            public function write(object $subject, MappingContext $context): mixed
            {
                assert($subject instanceof Amount);

                return ['cents' => $subject->amount];
            }
        };

        /** @And a mapper holding that registration */
        $mapper = Mapper::create()->withMapping(type: Amount::class, mapping: $amountMapping);

        /** @When a Mappable Wallet is built from a source whose Amount uses the registered shape */
        $wallet = $mapper->toObject(type: Wallet::class, source: ['balance' => ['cents' => 50]]);

        /** @Then the nested Amount is built through its registered mapping, not by reflection */
        self::assertEquals(new Wallet(balance: new Amount(amount: 50, currency: Currency::BRL)), $wallet);
    }

    public function testToObjectWhenBackedEnumPropertyReceivesNonScalarValueThenUnmappableSourceIsRaised(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @Then an unmappable-source exception is raised */
        $this->expectException(UnmappableSource::class);

        /** @When an Amount is hydrated with an array value for the backed enum property */
        $mapper->toObject(type: Amount::class, source: ['amount' => 1, 'currency' => ['not' => 'an enum']]);
    }

    public function testToObjectWhenDateTimePropertyReceivesUnparseableStringThenUnmappableSourceIsRaised(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @Then an unmappable-source exception is raised */
        $this->expectException(UnmappableSource::class);

        /** @When a Profile is hydrated with an unparseable createdAt string */
        $mapper->toObject(type: Profile::class, source: [
            'name'      => 'Gustavo',
            'title'     => null,
            'createdAt' => 'not-a-date',
            'severity'  => 'LOW'
        ]);
    }

    public function testToObjectWhenSourceClassHasStaticAndInstancePropertiesThenInstanceValuesAreHydrated(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a class declaring both a static and instance properties is hydrated */
        $tracker = $mapper->toObject(type: Tracker::class, source: ['hits' => 5, 'name' => 'taps']);

        /** @Then the instance properties carry the hydrated values */
        self::assertSame(5, $tracker->hits);
    }
}
