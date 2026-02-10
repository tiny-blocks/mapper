<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper;

use ArrayIterator;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Amount;
use Test\TinyBlocks\Mapper\Models\Articles;
use Test\TinyBlocks\Mapper\Models\Attributes;
use Test\TinyBlocks\Mapper\Models\Collection;
use Test\TinyBlocks\Mapper\Models\Country;
use Test\TinyBlocks\Mapper\Models\Currency;
use Test\TinyBlocks\Mapper\Models\Description;
use Test\TinyBlocks\Mapper\Models\Employee;
use Test\TinyBlocks\Mapper\Models\Employees;
use Test\TinyBlocks\Mapper\Models\Invoice;
use Test\TinyBlocks\Mapper\Models\Invoices;
use Test\TinyBlocks\Mapper\Models\InvoiceSummaries;
use Test\TinyBlocks\Mapper\Models\Member;
use Test\TinyBlocks\Mapper\Models\MemberId;
use Test\TinyBlocks\Mapper\Models\Members;
use Test\TinyBlocks\Mapper\Models\OrganizationId;
use Test\TinyBlocks\Mapper\Models\Product;
use Test\TinyBlocks\Mapper\Models\Products;
use Test\TinyBlocks\Mapper\Models\ProductStatus;
use Test\TinyBlocks\Mapper\Models\Tag;
use Test\TinyBlocks\Mapper\Models\Tags;
use Test\TinyBlocks\Mapper\Models\Uuid;
use TinyBlocks\Mapper\IterableMapper;
use TinyBlocks\Mapper\KeyPreservation;

final class CollectionMappingTest extends TestCase
{
    public function testCollectionIsEmpty(): void
    {
        /** @Given an empty Employees collection */
        $employees = Employees::createFrom(elements: []);

        /** @When mapping the Employees collection to an array */
        $actual = $employees->toArray();

        /** @Then the mapped array should be empty */
        self::assertSame([], $actual);

        /** @And the JSON representation should be an empty JSON array */
        self::assertJsonStringEqualsJsonString('[]', $employees->toJson());

        /** @And the Employees collection should have expected type and count */
        self::assertSame(0, $employees->count());
        self::assertSame(Employee::class, $employees->getType());
    }

    #[DataProvider('collectionOfObjectsProvider')]
    public function testCollectionOfObjects(string $type, IterableMapper $collection, array $expected): void
    {
        /** @Given a Collection of objects */
        /** @When mapping the collection to an array */
        $actual = $collection->toArray();

        /** @Then the mapped array should have expected values */
        self::assertSame($expected, $actual);

        /** @And the JSON representation should be the mapped JSON array */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $collection->toJson());

        /** @And the Collection should have expected type */
        self::assertSame($type, $collection->getType());
    }

    public function testCollectionOfScalars(): void
    {
        /** @Given an Attributes collection with integer values */
        $attributes = Attributes::createFrom(elements: [PHP_INT_MAX, 'red', 3.14, true, false, null, ['id' => 1]]);

        /** @When mapping the Attributes collection to an array */
        $actual = $attributes->toArray();

        /** @Then the mapped array should have expected values */
        $expected = [PHP_INT_MAX, 'red', 3.14, true, false, null, ['id' => 1]];

        self::assertSame($expected, $actual);

        /** @And the JSON representation should be the mapped JSON array */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $attributes->toJson());

        /** @And the Numbers collection should have expected type and count */
        self::assertSame(7, $attributes->count());
        self::assertSame('mixed', $attributes->getType());
    }

    public function testCollectionOfTraversable(): void
    {
        /** @Given an InvoiceSummaries collection with traversable invoices */
        $invoiceSummaries = InvoiceSummaries::createFrom(
            invoices: Invoices::createFrom(elements: [
                new Invoice(id: 'INV001', amount: 100.0, customer: 'Customer A'),
                new Invoice(id: 'INV002', amount: 150.5, customer: 'Customer B'),
                new Invoice(id: 'INV003', amount: 200.75, customer: 'Customer C')
            ])
        );

        /** @When mapping the InvoiceSummaries collection to an array */
        $actual = $invoiceSummaries->toArray();

        /** @Then the mapped array should have expected values */
        $expected = [
            'INV001' => ['id' => 'INV001', 'amount' => 100.0, 'customer' => 'Customer A'],
            'INV002' => ['id' => 'INV002', 'amount' => 150.5, 'customer' => 'Customer B'],
            'INV003' => ['id' => 'INV003', 'amount' => 200.75, 'customer' => 'Customer C']
        ];

        self::assertSame($expected, $actual);
    }

    #[DataProvider('collectionDiscardKeysProvider')]
    public function testCollectionDiscardKeys(IterableMapper $collection, array $expected): void
    {
        /** @Given a Collection with values having keys */
        /** @When mapping the Collection to an array */
        $actual = $collection->toArray(keyPreservation: KeyPreservation::DISCARD);

        /** @Then the mapped array should have expected values */
        self::assertSame($expected, $actual);

        /** @And the JSON representation should be the mapped JSON array */
        self::assertJsonStringEqualsJsonString(
            (string)json_encode($expected),
            $collection->toJson(keyPreservation: KeyPreservation::DISCARD)
        );
    }

    public function testCollectionGetTypeReturnsOwnClass(): void
    {
        /** @Given a Collection with arrays */
        $collection = Collection::createFrom(elements: [['id' => 1], ['id' => 2]]);

        /** @When mapping the Collection to an array */
        $actual = $collection->toArray();

        /** @Then the mapped array should have expected values */
        $expected = [['id' => 1], ['id' => 2]];

        self::assertSame($expected, $actual);

        /** @And the JSON representation should be the mapped JSON array */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $collection->toJson());
    }

    public function testCollectionWithNoConstructorElements(): void
    {
        /** @Given a Tags collection with a default Tag object */
        $tags = Tags::createFrom(elements: [new Tag()]);

        /** @When mapping the Tags collection to an array */
        $actual = $tags->toArray();

        /** @Then the mapped array should have expected values */
        $expected = [['name' => '', 'color' => 'gray']];

        self::assertSame($expected, $actual);

        /** @And the JSON representation should be the mapped JSON array */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $tags->toJson());

        /** @And the Tags collection should have expected type and count */
        self::assertSame(1, $tags->count());
        self::assertSame(Tag::class, $tags->getType());
    }

    public static function collectionOfObjectsProvider(): iterable
    {
        return [
            'Members collection'   => [
                'type'       => Member::class,
                'collection' => Members::createFrom(elements: [
                    new Member(
                        id: new MemberId(value: new Uuid(value: '88f15d3f-c9b9-4855-9778-5ba7926b6736')),
                        role: 'owner',
                        isOwner: true,
                        organizationId: new OrganizationId(
                            value: new Uuid(value: 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23')
                        )
                    ),
                    new Member(
                        id: new MemberId(value: new Uuid(value: 'c23b4c0a-f6d1-4b02-af2a-28b120a0ceb6')),
                        role: 'admin',
                        isOwner: false,
                        organizationId: new OrganizationId(
                            value: new Uuid(value: 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23')
                        )
                    )
                ]),
                'expected'   => [
                    [
                        'id'             => '88f15d3f-c9b9-4855-9778-5ba7926b6736',
                        'role'           => 'owner',
                        'isOwner'        => true,
                        'organizationId' => 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23'
                    ],
                    [
                        'id'             => 'c23b4c0a-f6d1-4b02-af2a-28b120a0ceb6',
                        'role'           => 'admin',
                        'isOwner'        => false,
                        'organizationId' => 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23'
                    ]
                ]
            ],
            'Articles collection'  => [
                'type'       => Articles::class,
                'collection' => Articles::createFrom(elements: [
                    ['id' => 1, 'title' => 'First Article'],
                    ['id' => 2, 'title' => 'Second Article']
                ]),
                'expected'   => [
                    ['id' => 1, 'title' => 'First Article'],
                    ['id' => 2, 'title' => 'Second Article']
                ]
            ],
            'Products collection'  => [
                'type'       => Product::class,
                'collection' => new Products(
                    items: [
                        new Product(
                            id: 1,
                            amount: Amount::from(value: 99.99, currency: Currency::USD),
                            description: new Description(text: 'A high-quality product'),
                            attributes: new ArrayIterator([
                                'color'   => 'red',
                                'size'    => 'M',
                                'inStock' => true
                            ]),
                            inventory: ['stock' => 100, 'warehouse' => 'A1'],
                            status: ProductStatus::ACTIVE,
                            createdAt: new DateTimeImmutable('2026-01-01T10:00:00+00:00')
                        ),
                        new Product(
                            id: 2,
                            amount: Amount::from(value: 149.99, currency: Currency::USD),
                            description: new Description(text: 'A premium product'),
                            attributes: new ArrayIterator([
                                'color'   => 'blue',
                                'size'    => 'L',
                                'inStock' => false
                            ]),
                            inventory: ['stock' => 0, 'warehouse' => 'B2'],
                            status: ProductStatus::INACTIVE,
                            createdAt: new DateTimeImmutable('2026-01-01T10:00:00+00:00')
                        )
                    ],
                    country: Country::UNITED_STATES
                ),
                'expected'   => [
                    [
                        'id'          => 1,
                        'amount'      => [
                            'value'    => 99.99,
                            'currency' => 'USD'
                        ],
                        'description' => 'A high-quality product',
                        'attributes'  => [
                            'color'   => 'red',
                            'size'    => 'M',
                            'inStock' => true
                        ],
                        'inventory'   => ['stock' => 100, 'warehouse' => 'A1'],
                        'status'      => 1,
                        'createdAt'   => '2026-01-01T10:00:00+00:00'
                    ],
                    [
                        'id'          => 2,
                        'amount'      => [
                            'value'    => 149.99,
                            'currency' => 'USD'
                        ],
                        'description' => 'A premium product',
                        'attributes'  => [
                            'color'   => 'blue',
                            'size'    => 'L',
                            'inStock' => false
                        ],
                        'inventory'   => ['stock' => 0, 'warehouse' => 'B2'],
                        'status'      => 2,
                        'createdAt'   => '2026-01-01T10:00:00+00:00'
                    ]
                ]
            ],
            'Employees collection' => [
                'type'       => Employee::class,
                'collection' => Employees::createFrom(elements: [
                    new Employee(name: 'Anne'),
                    new Employee(name: 'Gustavo', department: 'Technology'),
                    new Employee(name: 'John', department: 'Marketing', active: false)
                ]),
                'expected'   => [
                    ['name' => 'Anne', 'department' => 'general', 'active' => true],
                    ['name' => 'Gustavo', 'department' => 'Technology', 'active' => true],
                    ['name' => 'John', 'department' => 'Marketing', 'active' => false]
                ]
            ]
        ];
    }

    public static function collectionDiscardKeysProvider(): iterable
    {
        return [
            'Collection with string keys'             => [
                'collection' => Collection::createFrom(elements: [
                    ['id' => 1, 'name' => 'Gustavo'],
                    ['id' => 2, 'name' => 'Anne']
                ]),
                'expected'   => [
                    [1, 'Gustavo'],
                    [2, 'Anne']
                ]
            ],
            'Collection with integer keys'            => [
                'collection' => Collection::createFrom(elements: [
                    10 => 'first',
                    20 => 'second',
                    30 => 'third'
                ]),
                'expected'   => [
                    'first',
                    'second',
                    'third'
                ]
            ],
            'Collection of objects with string keys'  => [
                'collection' => Collection::createFrom(elements: [
                    'gustavo' => new MemberId(value: new Uuid(value: '88f15d3f-c9b9-4855-9778-5ba7926b6736')),
                    'anne'    => new MemberId(value: new Uuid(value: 'c23b4c0a-f6d1-4b02-af2a-28b120a0ceb6'))
                ]),
                'expected'   => [
                    '88f15d3f-c9b9-4855-9778-5ba7926b6736',
                    'c23b4c0a-f6d1-4b02-af2a-28b120a0ceb6'
                ]
            ],
            'Collection of objects with integer keys' => [
                'collection' => Collection::createFrom(elements: [
                    100 => new Uuid(value: '88f15d3f-c9b9-4855-9778-5ba7926b6736'),
                    200 => new Uuid(value: 'c23b4c0a-f6d1-4b02-af2a-28b120a0ceb6')
                ]),
                'expected'   => [
                    '88f15d3f-c9b9-4855-9778-5ba7926b6736',
                    'c23b4c0a-f6d1-4b02-af2a-28b120a0ceb6'
                ]
            ]
        ];
    }
}
