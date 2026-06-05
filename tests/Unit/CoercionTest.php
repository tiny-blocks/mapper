<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Unit;

use PHPUnit\Framework\TestCase;
use stdClass;
use Test\TinyBlocks\Mapper\Models\Address;
use Test\TinyBlocks\Mapper\Models\Amount;
use Test\TinyBlocks\Mapper\Models\Camera;
use Test\TinyBlocks\Mapper\Models\Catalog;
use Test\TinyBlocks\Mapper\Models\Contact;
use Test\TinyBlocks\Mapper\Models\Currency;
use Test\TinyBlocks\Mapper\Models\Customer;
use Test\TinyBlocks\Mapper\Models\Holder;
use Test\TinyBlocks\Mapper\Models\Inventory;
use Test\TinyBlocks\Mapper\Models\MemberId;
use Test\TinyBlocks\Mapper\Models\OrderStatus;
use Test\TinyBlocks\Mapper\Models\Owner;
use Test\TinyBlocks\Mapper\Models\Pair;
use Test\TinyBlocks\Mapper\Models\Profile;
use Test\TinyBlocks\Mapper\Models\Severity;
use Test\TinyBlocks\Mapper\Models\Shelf;
use Test\TinyBlocks\Mapper\Models\Studio;
use TinyBlocks\Mapper\Configuration;
use TinyBlocks\Mapper\Exceptions\UnmappableSource;
use TinyBlocks\Mapper\Layout;
use TinyBlocks\Mapper\Mapper;
use TinyBlocks\Mapper\SnakeCase;

final class CoercionTest extends TestCase
{
    public function testBuildFromWhenSourceHasExtraKeyThenItIsIgnored(): void
    {
        /** @When a Mappable is built with an extra unknown key */
        $contact = Contact::buildFrom(
            source: ['fullName' => 'Gustavo', 'email' => 'g@example.com', 'extra' => 'ignored']
        );

        /** @Then the Mappable instance is built without complaining */
        self::assertEquals(new Contact(email: 'g@example.com', fullName: 'Gustavo'), $contact);
    }

    public function testToArrayWhenChainingOmittingThenAllFieldsAreAbsent(): void
    {
        /** @Given an address instance */
        $address = new Address(city: 'São Paulo', street: 'Av. Paulista');

        /** @When omitting is chained twice */
        $array = $address->toArray(
            configuration: Configuration::default()->omitting('street')->omitting('city')
        );

        /** @Then both fields are absent */
        self::assertSame([], $array);
    }

    public function testToArrayWhenPureEnumIsTopLevelThenItsNameIsEmitted(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a pure enum is serialized as the top-level subject */
        $array = $mapper->toArray(source: Severity::HIGH);

        /** @Then the pure enum is emitted as its wrapped case name */
        self::assertSame(['HIGH'], $array);
    }

    public function testToArrayWhenBackedEnumIsTopLevelThenItsValueIsEmitted(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a backed enum is serialized as the top-level subject */
        $array = $mapper->toArray(source: OrderStatus::PENDING);

        /** @Then the backed enum is emitted as a wrapped value distinct from its name */
        self::assertSame(['pending'], $array);
    }

    public function testToArrayWhenOmittingMultipleFieldsAtOnceThenAllAreAbsent(): void
    {
        /** @Given an address instance */
        $address = new Address(city: 'São Paulo', street: 'Av. Paulista');

        /** @When omitting is given multiple fields in a single call */
        $array = $address->toArray(
            configuration: Configuration::default()->omitting('street', 'city')
        );

        /** @Then all named fields are absent */
        self::assertSame([], $array);
    }

    public function testToArrayWhenObjectHasArrayOfEnumsThenElementsAreSerialized(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When an Inventory containing an array of enums is serialized */
        $array = $mapper->toArray(
            source: new Inventory(stocks: [Currency::BRL, Currency::USD], location: 'warehouse-1')
        );

        /** @Then nested array elements are serialized through the engine */
        self::assertSame(['stocks' => ['BRL', 'USD'], 'location' => 'warehouse-1'], $array);
    }

    public function testToObjectWhenLayoutHasDateTimePropertyThenItIsTreatedAsLeaf(): void
    {
        /** @Given a mapper with snake_case naming and a Layout on Profile */
        $mapper = Mapper::create()
            ->withNaming(namingStrategy: SnakeCase::create())
            ->withMapping(type: Profile::class, mapping: Layout::from(paths: []));

        /** @When a Profile is hydrated from a flat row */
        $profile = $mapper->toObject(type: Profile::class, source: [
            'name'       => 'Gustavo',
            'title'      => null,
            'created_at' => '2024-01-02T03:04:05+00:00',
            'severity'   => 'HIGH'
        ]);

        /** @Then the DateTime leaf was built from a single derived column */
        self::assertEquals('2024-01-02T03:04:05+00:00', $profile->createdAt->format(DATE_ATOM));
    }

    public function testToObjectWhenStrictModeAndOnlyKnownKeysThenNoExceptionIsRaised(): void
    {
        /** @Given a strict mapper */
        $mapper = Mapper::create()->rejectingUnknownKeys();

        /** @When an Amount is hydrated with only known keys */
        $amount = $mapper->toObject(type: Amount::class, source: ['amount' => 5, 'currency' => 'BRL']);

        /** @Then no exception is raised and the values match */
        self::assertEquals(new Amount(amount: 5, currency: Currency::BRL), $amount);
    }

    public function testToObjectWhenTargetIsMappableThenActiveNamingDrivesKeyResolution(): void
    {
        /** @Given a snake_case mapper */
        $mapper = Mapper::create()->withNaming(namingStrategy: SnakeCase::create());

        /** @When a Mappable with camelCase properties is hydrated from a snake_case source */
        $contact = $mapper->toObject(
            type: Contact::class,
            source: ['full_name' => 'Gustavo', 'email' => 'g@example.com']
        );

        /** @Then the Mappable recurses through the active context and resolves keys by its naming */
        self::assertEquals(new Contact(email: 'g@example.com', fullName: 'Gustavo'), $contact);
    }

    public function testToObjectWhenNullableClassPropertyAndNullSourceThenPropertyIsNull(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a Holder is hydrated with a null memberId */
        $holder = $mapper->toObject(type: Holder::class, source: ['memberId' => null]);

        /** @Then the memberId is null */
        self::assertNull($holder->memberId);
    }

    public function testToArrayWhenObjectHasNamedKeyArrayAndPreservingKeysThenKeysAreKept(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When an Inventory carrying named-key stocks is serialized */
        $array = $mapper->toArray(
            source: new Inventory(stocks: ['alpha' => Currency::BRL, 'beta' => Currency::USD], location: 'w-1')
        );

        /** @Then the stocks array retains its original keys */
        self::assertSame(['stocks' => ['alpha' => 'BRL', 'beta' => 'USD'], 'location' => 'w-1'], $array);
    }

    public function testToObjectWhenLayoutHasTraversableTypedPropertyThenItIsTreatedAsLeaf(): void
    {
        /** @Given a mapper with a Layout on a class holding a Traversable-typed property */
        $mapper = Mapper::create()->withMapping(type: Shelf::class, mapping: Layout::from(paths: []));

        /** @When a Shelf is hydrated from a row whose catalog column is the catalog source */
        $shelf = $mapper->toObject(type: Shelf::class, source: [
            'name'    => 'shelf-1',
            'catalog' => ['label' => 'l-1', 'reference' => 'r-1']
        ]);

        /** @Then the Traversable property was treated as a leaf and built from the catalog value */
        self::assertEquals(
            new Shelf(name: 'shelf-1', catalog: new Catalog(label: 'l-1', reference: 'r-1')),
            $shelf
        );
    }

    public function testToObjectWhenLayoutHasMappablePropertyThenItIsDecomposedByReflection(): void
    {
        /** @Given a mapper with a Layout on Customer */
        $mapper = Mapper::create()->withMapping(
            type: Customer::class,
            mapping: Layout::from(paths: [])
        );

        /** @When a Customer is hydrated from a flat row whose billing columns are derived */
        $customer = $mapper->toObject(type: Customer::class, source: [
            'identifier'    => 'c-1',
            'billingStreet' => 'Av. Paulista',
            'billingCity'   => 'São Paulo'
        ]);

        /** @Then the Mappable property was decomposed into its derived columns through reflection */
        self::assertEquals(
            new Customer(billing: new Address(city: 'São Paulo', street: 'Av. Paulista'), identifier: 'c-1'),
            $customer
        );
    }

    public function testToObjectWhenLayoutHasSingleBuiltinPropertyClassThenItIsTreatedAsLeaf(): void
    {
        /** @Given a mapper with snake_case naming and a Layout on Owner */
        $mapper = Mapper::create()
            ->withNaming(namingStrategy: SnakeCase::create())
            ->withMapping(type: Owner::class, mapping: Layout::from(paths: []));

        /** @When an Owner is hydrated through the Layout */
        $owner = $mapper->toObject(type: Owner::class, source: ['member_id' => 'm-1', 'name' => 'Alice']);

        /** @Then the MemberId leaf was read from a single column without expansion */
        self::assertEquals(new Owner(name: 'Alice', memberId: new MemberId(value: 'm-1')), $owner);
    }

    public function testToObjectWhenLayoutWithIdentityAndCamelCasePropsThenColumnsAreDerived(): void
    {
        /** @Given a mapper with identity naming and a Layout on Studio */
        $mapper = Mapper::create()->withMapping(type: Studio::class, mapping: Layout::from(paths: []));

        /** @When a Studio is hydrated from a camelCase-concatenated flat row */
        $studio = $mapper->toObject(type: Studio::class, source: [
            'mainCameraSerialNumber' => 'sn-1',
            'mainCameraShotCount'    => 7,
            'tag'                    => 'studio-a'
        ]);

        /** @Then the nested Camera columns are read by camelCase concatenation */
        self::assertEquals(
            new Studio(tag: 'studio-a', mainCamera: new Camera(shotCount: 7, serialNumber: 'sn-1')),
            $studio
        );
    }

    public function testToArrayWhenObjectHasNamedKeyArrayAndDiscardingKeysThenKeysAreRenumbered(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When an Inventory carrying named-key stocks is serialized discarding keys */
        $array = $mapper->toArray(
            source: new Inventory(stocks: ['alpha' => Currency::BRL, 'beta' => Currency::USD], location: 'w-1'),
            configuration: Configuration::default()->discardingKeys()
        );

        /** @Then the stocks array is reindexed */
        self::assertSame(['stocks' => ['BRL', 'USD'], 'location' => 'w-1'], $array);
    }

    public function testToObjectWhenValueIsNullForNonNullablePropertyThenUnmappableSourceIsRaised(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @Then an unmappable-source exception identifying the null source is raised */
        $this->expectException(UnmappableSource::class);
        $this->expectExceptionMessage('from null.');

        /** @When a non-nullable property receives a null source value */
        $mapper->toObject(type: Amount::class, source: ['amount' => 1, 'currency' => null]);
    }

    public function testToObjectWhenSourceMissesFirstKeyAndCarriesSecondThenLaterPropertyIsStillRead(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a Pair is hydrated with only the second key */
        $pair = $mapper->toObject(type: Pair::class, source: ['second' => 'B']);

        /** @Then the later property carries its value despite the earlier one missing */
        self::assertSame('B', $pair->second);
    }

    public function testToObjectWhenSourceMissesKeyForNonNullablePropertyThenPropertyIsUninitialized(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When an Amount is hydrated with only one key */
        $amount = $mapper->toObject(type: Amount::class, source: ['amount' => 50]);

        /** @Then the present property is set and the missing one stays uninitialized */
        self::assertSame(50, $amount->amount);
    }

    public function testToObjectWhenForeignObjectIsGivenForAClassPropertyThenUnmappableSourceIsRaised(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @Then an unmappable-source exception is raised */
        $this->expectException(UnmappableSource::class);

        /** @When a foreign object value is provided for a class-typed property */
        $mapper->toObject(type: Owner::class, source: ['memberId' => new stdClass(), 'name' => 'Alice']);
    }
}
