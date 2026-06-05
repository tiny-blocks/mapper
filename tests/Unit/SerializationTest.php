<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Unit;

use ArrayIterator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Account;
use Test\TinyBlocks\Mapper\Models\Address;
use Test\TinyBlocks\Mapper\Models\Amount;
use Test\TinyBlocks\Mapper\Models\Charge;
use Test\TinyBlocks\Mapper\Models\Cnpj;
use Test\TinyBlocks\Mapper\Models\Contact;
use Test\TinyBlocks\Mapper\Models\Counter;
use Test\TinyBlocks\Mapper\Models\Cpf;
use Test\TinyBlocks\Mapper\Models\Currency;
use Test\TinyBlocks\Mapper\Models\DebitCard;
use Test\TinyBlocks\Mapper\Models\MemberId;
use Test\TinyBlocks\Mapper\Models\Money;
use Test\TinyBlocks\Mapper\Models\Owner;
use Test\TinyBlocks\Mapper\Models\PaymentMethod;
use Test\TinyBlocks\Mapper\Models\Pix;
use Test\TinyBlocks\Mapper\Models\Positive;
use Test\TinyBlocks\Mapper\Models\Profile;
use Test\TinyBlocks\Mapper\Models\Refund;
use Test\TinyBlocks\Mapper\Models\Refunds;
use Test\TinyBlocks\Mapper\Models\Scalars;
use Test\TinyBlocks\Mapper\Models\Severity;
use Test\TinyBlocks\Mapper\Models\SpecializedTag;
use Test\TinyBlocks\Mapper\Models\TaxId;
use Test\TinyBlocks\Mapper\Models\Wallet;
use TinyBlocks\Mapper\Configuration;
use TinyBlocks\Mapper\Exceptions\UnknownSubtype;
use TinyBlocks\Mapper\Exceptions\UnmappableSource;
use TinyBlocks\Mapper\Identity;
use TinyBlocks\Mapper\Layout;
use TinyBlocks\Mapper\Mapper;
use TinyBlocks\Mapper\Mapping;
use TinyBlocks\Mapper\MappingContext;
use TinyBlocks\Mapper\SnakeCase;
use TinyBlocks\Mapper\Subtype;

final class SerializationTest extends TestCase
{
    public function testToJsonWhenSourceHasUnicodeThenJsonIsUnescaped(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a Profile with a non-ASCII name is serialized to JSON */
        $json = $mapper->toJson(source: new Profile(
            name: 'José',
            title: null,
            severity: Severity::LOW,
            createdAt: new DateTimeImmutable('2024-01-02T03:04:05+00:00')
        ));

        /** @Then unicode escape is not applied */
        self::assertStringContainsString('"name":"José"', $json);
    }

    public function testToJsonOrNullWhenSourceIsNullThenNullIsReturned(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When toJsonOrNull is called with a null source */
        $json = $mapper->toJsonOrNull(source: null);

        /** @Then the result is null */
        self::assertNull($json);
    }

    public function testToArrayOrNullWhenSourceIsNullThenNullIsReturned(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When toArrayOrNull is called with a null source */
        $array = $mapper->toArrayOrNull(source: null);

        /** @Then the result is null */
        self::assertNull($array);
    }

    public function testToArrayWhenSnakeCaseNamingThenKeysAreSnakeCased(): void
    {
        /** @Given a mapper with snake_case naming */
        $mapper = Mapper::create()->withNaming(namingStrategy: SnakeCase::create());

        /** @When a Profile is serialized */
        $array = $mapper->toArray(source: new Profile(
            name: 'Gustavo',
            title: null,
            severity: Severity::HIGH,
            createdAt: new DateTimeImmutable('2024-01-02T03:04:05+00:00')
        ));

        /** @Then nested keys use snake_case */
        self::assertSame(
            [
                'name'       => 'Gustavo',
                'title'      => null,
                'severity'   => 'HIGH',
                'created_at' => '2024-01-02T03:04:05+00:00'
            ],
            $array
        );
    }

    public function testToArrayWhenSingleValuePropertyThenValueIsUnwrapped(): void
    {
        /** @Given a mapper with identity naming */
        $mapper = Mapper::create();

        /** @When an Owner with a single-value MemberId is serialized */
        $array = $mapper->toArray(
            source: new Owner(name: 'Alice', memberId: new MemberId(value: 'm-1'))
        );

        /** @Then the MemberId appears as its unwrapped scalar */
        self::assertSame(['name' => 'Alice', 'memberId' => 'm-1'], $array);
    }

    public function testToArrayWhenIdentityNamingThenKeysMatchPropertyNames(): void
    {
        /** @Given a mapper with identity naming */
        $mapper = Mapper::create();

        /** @When an Amount is serialized */
        $array = $mapper->toArray(source: new Amount(amount: 50, currency: Currency::BRL));

        /** @Then keys match the property names */
        self::assertSame(['amount' => 50, 'currency' => 'BRL'], $array);
    }

    public function testToJsonOrNullWhenSourceIsPresentThenJsonMatchesToJson(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @And an Amount source */
        $amount = new Amount(amount: 50, currency: Currency::BRL);

        /** @And a configuration that omits the currency */
        $configuration = Configuration::default()->omitting('currency');

        /** @When the present source is serialized through toJsonOrNull */
        $json = $mapper->toJsonOrNull(source: $amount, configuration: $configuration);

        /** @Then the result matches what toJson returns for the same input */
        self::assertSame($mapper->toJson(source: $amount, configuration: $configuration), $json);
    }

    public function testToArrayWhenTraversableAndPreservingKeysThenKeysAreKept(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @And a Refunds collection holding two refunds */
        $refunds = Refunds::createFrom(elements: [
            'first'  => new Refund(amount: new Amount(amount: 1, currency: Currency::BRL), reference: 'r-1'),
            'second' => new Refund(amount: new Amount(amount: 2, currency: Currency::BRL), reference: 'r-2')
        ]);

        /** @When the collection is serialized preserving keys */
        $array = $mapper->toArray(source: $refunds);

        /** @Then the resulting array uses the original keys */
        self::assertSame(['first', 'second'], array_keys($array));
    }

    public function testToArrayOrNullWhenSourceIsPresentThenArrayMatchesToArray(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @And an Amount source */
        $amount = new Amount(amount: 50, currency: Currency::BRL);

        /** @And a configuration that omits the currency */
        $configuration = Configuration::default()->omitting('currency');

        /** @When the present source is serialized through toArrayOrNull */
        $array = $mapper->toArrayOrNull(source: $amount, configuration: $configuration);

        /** @Then the result matches what toArray returns for the same input */
        self::assertSame($mapper->toArray(source: $amount, configuration: $configuration), $array);
    }

    public function testToArrayWhenSourceIsBackedEnumPropertyThenValueIsEmitted(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When an Amount is serialized */
        $array = $mapper->toArray(source: new Amount(amount: 1, currency: Currency::USD));

        /** @Then the backed enum is emitted as its value */
        self::assertSame(['amount' => 1, 'currency' => 'USD'], $array);
    }

    public function testToArrayWhenSubtypeRegisteredThenDiscriminatorIsWrittenOnce(): void
    {
        /** @Given a mapper with a Subtype mapping on PaymentMethod */
        $mapper = Mapper::create()->withMapping(
            type: PaymentMethod::class,
            mapping: Subtype::by(field: 'type', types: [Pix::class])
        );

        /** @And a Charge holding a Pix */
        $charge = new Charge(
            amount: new Amount(amount: 50, currency: Currency::BRL),
            refunds: Refunds::createFrom(elements: []),
            paymentMethod: new Pix(payerId: 'alice')
        );

        /** @When the Charge is serialized */
        $array = $mapper->toArray(
            source: $charge,
            configuration: Configuration::default()->omitting('refunds')
        );

        /** @Then the discriminator type field is written exactly once */
        self::assertSame(['payerId' => 'alice', 'type' => 'pix'], $array['paymentMethod']);
    }

    public function testToArrayWhenSourceClassDeclaresAStaticPropertyThenItIsIgnored(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a Counter holding a static and an instance property is serialized */
        $array = $mapper->toArray(source: new Counter(label: 'taps', value: 7));

        /** @Then the static property is absent and only the instance values are emitted */
        self::assertSame(['label' => 'taps', 'value' => 7], $array);
    }

    public function testToArrayWhenSubclassInheritsParentPropertyThenItIsEmittedOnce(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a SpecializedTag that inherits its parent's label is serialized */
        $array = $mapper->toArray(source: new SpecializedTag(label: 'urgent', level: 2));

        /** @Then the inherited property appears exactly once alongside the child property */
        self::assertSame(['level' => 2, 'label' => 'urgent'], $array);
    }

    public function testToArrayWhenTraversableAndDiscardingKeysThenKeysAreRenumbered(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @And a Refunds collection holding two refunds with named keys */
        $refunds = Refunds::createFrom(elements: [
            'first'  => new Refund(amount: new Amount(amount: 1, currency: Currency::BRL), reference: 'r-1'),
            'second' => new Refund(amount: new Amount(amount: 2, currency: Currency::BRL), reference: 'r-2')
        ]);

        /** @When the collection is serialized discarding keys */
        $array = $mapper->toArray(source: $refunds, configuration: Configuration::default()->discardingKeys());

        /** @Then the resulting array is reindexed */
        self::assertSame([0, 1], array_keys($array));
    }

    public function testToArrayWhenOmittingMiddlePropertyThenLaterPropertiesStillEmit(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a Profile is serialized omitting the title */
        $array = $mapper->toArray(
            source: new Profile(
                name: 'Gustavo',
                title: 'Engineer',
                severity: Severity::LOW,
                createdAt: new DateTimeImmutable('2024-01-02T03:04:05+00:00')
            ),
            configuration: Configuration::default()->omitting('title')
        );

        /** @Then only the omitted property is absent and the later ones remain */
        self::assertSame(
            ['name' => 'Gustavo', 'severity' => 'LOW', 'createdAt' => '2024-01-02T03:04:05+00:00'],
            $array
        );
    }

    public function testToJsonWhenValueCannotBeEncodedAsJsonThenUnmappableSourceIsRaised(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @And a Scalars instance carrying a not-a-number ratio that JSON cannot encode */
        $scalars = new Scalars(count: 0, label: 'x', ratio: NAN, active: true);

        /** @Then an unmappable-source exception is raised */
        $this->expectException(UnmappableSource::class);

        /** @When the value is converted to JSON */
        $mapper->toJson(source: $scalars);
    }

    public function testToArrayWhenTopLevelSourceIsMappableThenItsPortableShapeIsReturned(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a Mappable instance is serialized as the top-level subject */
        $array = $mapper->toArray(source: new Address(city: 'São Paulo', street: 'Av. Paulista'));

        /** @Then the Mappable is serialized through the engine into its portable shape */
        self::assertSame(['city' => 'São Paulo', 'street' => 'Av. Paulista'], $array);
    }

    public function testToArrayWhenSubtypeByDefaultNamingThenDiscriminatorIsDerivedAsSnakeCase(): void
    {
        /** @Given a mapper with a convention-based Subtype mapping using default naming */
        $mapper = Mapper::create()->withMapping(
            type: PaymentMethod::class,
            mapping: Subtype::by(field: 'type', types: [Pix::class, DebitCard::class])
        );

        /** @When a DebitCard is serialized */
        $array = $mapper->toArray(source: new DebitCard(cardNumber: '4242'));

        /** @Then the discriminator is the snake_case form of the short name */
        self::assertSame(['cardNumber' => '4242', 'type' => 'debit_card'], $array);
    }

    public function testToArrayWhenTopLevelSourceSerializesToAScalarThenScalarIsWrappedInArray(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a single-property value object whose engine output is a scalar is serialized */
        $array = $mapper->toArray(source: new MemberId(value: 'm-1'));

        /** @Then the scalar is wrapped in an array */
        self::assertSame(['m-1'], $array);
    }

    public function testToArrayWhenLayoutSubjectHasUninitializedLeafPropertiesThenColumnsAreNull(): void
    {
        /** @Given a default mapper used to build a Profile whose name and title were never initialized */
        $profile = Mapper::create()->toObject(type: Profile::class, source: [
            'createdAt' => '2024-01-02T03:04:05+00:00',
            'severity'  => 'LOW'
        ]);

        /** @And a second mapper with a Layout on Profile */
        $mapper = Mapper::create()->withMapping(type: Profile::class, mapping: Layout::from(paths: []));

        /** @When the partially initialized Profile is serialized */
        $array = $mapper->toArray(source: $profile);

        /** @Then the uninitialized columns are emitted as null and the initialized ones are preserved */
        self::assertSame(
            [
                'name'      => null,
                'title'     => null,
                'severity'  => 'LOW',
                'createdAt' => '2024-01-02T03:04:05+00:00'
            ],
            $array
        );
    }

    public function testToArrayWhenMultiPropertyValueObjectHasPrivatePropertiesThenEachIsEmitted(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a Money with a private constructor and private properties is serialized */
        $array = $mapper->toArray(source: Money::of(amount: Positive::from(value: 50), currency: Currency::BRL));

        /** @Then every declared property is emitted under its property name */
        self::assertSame(['amount' => 50, 'currency' => 'BRL'], $array);
    }

    public function testToArrayWhenSubtypeByExplicitNamingThenDiscriminatorIsTheVerbatimShortName(): void
    {
        /** @Given a mapper with a convention-based Subtype mapping using identity naming */
        $mapper = Mapper::create()->withMapping(
            type: PaymentMethod::class,
            mapping: Subtype::by(field: 'type', types: [Pix::class, DebitCard::class], naming: Identity::create())
        );

        /** @When a DebitCard is serialized */
        $array = $mapper->toArray(source: new DebitCard(cardNumber: '4242'));

        /** @Then the discriminator is the verbatim short name */
        self::assertSame(['cardNumber' => '4242', 'type' => 'DebitCard'], $array);
    }

    public function testToArrayWhenSourceIsPlainTraversableAndDiscardingKeysThenValuesAreReindexed(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @And a plain ArrayIterator with named keys */
        $iterator = new ArrayIterator(['a' => 1, 'b' => 2]);

        /** @When the iterator is serialized through a wrapper holding it */
        $array = $mapper->toArray(
            source: $iterator,
            configuration: Configuration::default()->discardingKeys()
        );

        /** @Then the iterator is emitted as reindexed values */
        self::assertSame([1, 2], $array);
    }

    public function testToArrayWhenNestedMappableUnderSnakeCaseMapperThenInnerKeysFollowActiveNaming(): void
    {
        /** @Given a snake_case mapper */
        $mapper = Mapper::create()->withNaming(namingStrategy: SnakeCase::create());

        /** @And an Account containing a Mappable Contact */
        $account = new Account(
            accountId: 'a-1',
            primaryContact: new Contact(email: 'g@example.com', fullName: 'Gustavo')
        );

        /** @When the Account is serialized */
        $array = $mapper->toArray(source: $account);

        /** @Then the nested Mappable recurses through the active context and follows its naming */
        self::assertSame(
            [
                'account_id'      => 'a-1',
                'primary_contact' => ['email' => 'g@example.com', 'full_name' => 'Gustavo']
            ],
            $array
        );
    }

    public function testToArrayWhenMappableContainsTypeWithRegisteredMappingThenNestedMappingIsApplied(): void
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

        /** @When a Mappable Wallet carrying that Amount is serialized */
        $array = $mapper->toArray(source: new Wallet(balance: new Amount(amount: 50, currency: Currency::BRL)));

        /** @Then the nested Amount is emitted through its registered mapping, not by reflection */
        self::assertSame(['balance' => ['cents' => 50]], $array);
    }

    public function testToArrayWhenSubtypeConcreteHasPrivatePropertiesThenBodyIsWrittenWithDiscriminator(): void
    {
        /** @Given a mapper with a Subtype mapping on TaxId */
        $mapper = Mapper::create()->withMapping(
            type: TaxId::class,
            mapping: Subtype::by(field: 'type', types: [Cpf::class, Cnpj::class])
        );

        /** @When a Cnpj whose private properties form its body is serialized */
        $array = $mapper->toArray(source: Cnpj::of(base: '12345678', branch: '0001'));

        /** @Then the full private body is written alongside the discriminator */
        self::assertSame(['base' => '12345678', 'branch' => '0001', 'type' => 'cnpj'], $array);
    }

    public function testToArrayWhenExactTypeIsRegisteredAfterAParentThenItsMappingWinsOverTheParentMapping(): void
    {
        /** @Given a custom Mapping for the exact concrete type that produces a distinct shape */
        $pixOnlyMapping = new class () implements Mapping {
            public function read(mixed $source, MappingContext $context): object
            {
                return new Pix(payerId: 'decoded');
            }

            public function write(object $subject, MappingContext $context): mixed
            {
                assert($subject instanceof Pix);

                return ['exactPayer' => $subject->payerId];
            }
        };

        /** @And a mapper registering the parent type before the exact subtype */
        $mapper = Mapper::create()
            ->withMapping(
                type: PaymentMethod::class,
                mapping: Subtype::by(field: 'type', types: [Pix::class])
            )
            ->withMapping(type: Pix::class, mapping: $pixOnlyMapping);

        /** @When a Pix instance is serialized */
        $array = $mapper->toArray(source: new Pix(payerId: 'alice'));

        /** @Then the exact-type mapping is selected over the earlier parent mapping */
        self::assertSame(['exactPayer' => 'alice'], $array);
    }

    public function testToArrayWhenMappableTypeHasACustomMappingRegisteredThenRegisteredMappingTakesPrecedence(): void
    {
        /** @Given a custom Mapping registered for a Mappable type that emits a distinct shape */
        $mapping = new class () implements Mapping {
            public function read(mixed $source, MappingContext $context): object
            {
                return new Address(city: 'from-mapping', street: 'from-mapping');
            }

            public function write(object $subject, MappingContext $context): mixed
            {
                return ['from' => 'mapping'];
            }
        };

        /** @And a mapper holding that registration */
        $mapper = Mapper::create()->withMapping(type: Address::class, mapping: $mapping);

        /** @When the Mappable instance is serialized as the top-level subject */
        $array = $mapper->toArray(source: new Address(city: 'São Paulo', street: 'Av. Paulista'));

        /** @Then the registered mapping wins over the Mappable self-mapping */
        self::assertSame(['from' => 'mapping'], $array);
    }

    public function testToArrayWhenSubtypeRegisteredAndSubjectIsUnregisteredSubclassThenUnknownSubtypeIsRaised(): void
    {
        /** @Given a mapper with a Subtype mapping that only knows the Pix case */
        $mapper = Mapper::create()->withMapping(
            type: PaymentMethod::class,
            mapping: Subtype::by(field: 'type', types: [Pix::class])
        );

        /** @Then a missing-subtype exception is raised for the unregistered subclass */
        $this->expectException(UnknownSubtype::class);

        /** @When a DebitCard instance is serialized under that mapping */
        $mapper->toArray(source: new DebitCard(cardNumber: '4242'));
    }
}
