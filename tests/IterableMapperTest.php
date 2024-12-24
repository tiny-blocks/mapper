<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use ArrayIterator;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Mapper\Models\Amount;
use TinyBlocks\Mapper\Models\Currency;
use TinyBlocks\Mapper\Models\Decimal;
use TinyBlocks\Mapper\Models\Dragon;
use TinyBlocks\Mapper\Models\DragonSkills;
use TinyBlocks\Mapper\Models\DragonType;
use TinyBlocks\Mapper\Models\ExpirationDate;
use TinyBlocks\Mapper\Models\InvoiceSummaries;
use TinyBlocks\Mapper\Models\Order;
use TinyBlocks\Mapper\Models\Product;
use TinyBlocks\Mapper\Models\Shipping;
use TinyBlocks\Mapper\Models\ShippingAddress;
use TinyBlocks\Mapper\Models\ShippingAddresses;
use TinyBlocks\Mapper\Models\ShippingCountry;
use TinyBlocks\Mapper\Models\ShippingState;

final class IterableMapperTest extends TestCase
{
    #[DataProvider('dataProviderForToJson')]
    public function testCollectionToJson(iterable $elements, string $expected): void
    {
        /** @Given a collection with elements */
        $collection = InvoiceSummaries::createFrom(elements: $elements);

        /** @When converting the collection to JSON */
        $actual = $collection->toJson();

        /** @Then the result should match the expected */
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    #[DataProvider('dataProviderForToArray')]
    public function testCollectionToArray(iterable $elements, iterable $expected): void
    {
        /** @Given a collection with elements */
        $collection = InvoiceSummaries::createFrom(elements: $elements);

        /** @When converting the collection to array */
        $actual = $collection->toArray();

        /** @Then the result should match the expected */
        self::assertSame($expected, $actual);
        self::assertSame(count($expected), $collection->count());
    }

    #[DataProvider('dataProviderForToJsonDiscardKeys')]
    public function testCollectionToJsonDiscardKeys(iterable $elements, string $expected): void
    {
        /** @Given a collection with elements */
        $collection = InvoiceSummaries::createFrom(elements: $elements);

        /** @When converting the collection to JSON while discarding keys */
        $actual = $collection->toJson(keyPreservation: KeyPreservation::DISCARD);

        /** @Then the result should match the expected */
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    #[DataProvider('dataProviderForToJsonPreserveKeys')]
    public function testCollectionToJsonPreserveKeys(iterable $elements, string $expected): void
    {
        /** @Given a collection with elements */
        $collection = InvoiceSummaries::createFrom(elements: $elements);

        /** @When converting the collection to JSON while preserve keys */
        $actual = $collection->toJson();

        /** @Then the result should match the expected */
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    #[DataProvider('dataProviderForToArrayDiscardKeys')]
    public function testCollectionToArrayDiscardKeys(iterable $elements, iterable $expected): void
    {
        /** @Given a collection with elements */
        $collection = InvoiceSummaries::createFrom(elements: $elements);

        /** @When converting the collection to array while discarding keys */
        $actual = $collection->toArray(keyPreservation: KeyPreservation::DISCARD);

        /** @Then the result should match the expected */
        self::assertSame($expected, $actual);
        self::assertSame(count($expected), $collection->count());
    }

    #[DataProvider('dataProviderForToArrayPreserveKeys')]
    public function testCollectionToArrayPreserveKeys(iterable $elements, iterable $expected): void
    {
        /** @Given a collection with elements */
        $collection = InvoiceSummaries::createFrom(elements: $elements);

        /** @When converting the collection to array while preserve keys */
        $actual = $collection->toArray();

        /** @Then the result should match the expected */
        self::assertSame($expected, $actual);
        self::assertSame(count($expected), $collection->count());
    }

    public function testInvalidCollectionValueToArrayReturnsEmptyArray(): void
    {
        /** @Given a collection with an invalid item (e.g., a function that cannot be serialized) */
        $collection = InvoiceSummaries::createFrom(elements: [fn(): null => null, fn(): null => null]);

        /** @When attempting to serialize the collection containing the invalid items */
        $actual = $collection->toJson();

        /** @Then the invalid item should be serialized as an empty array in the JSON output */
        self::assertSame('[]', $actual);
    }

    public static function dataProviderForToJson(): iterable
    {
        return [
            'Empty collection'                        => [
                'elements' => [],
                'expected' => '[]'
            ],
            'Order collection'                        => [
                'elements' => [
                    new Order(
                        id: '2c485713-521c-4d91-b9e7-1294f132ad2e',
                        items: (static function () {
                            yield ['name' => 'Macbook Pro'];
                            yield ['name' => 'iPhone XYZ'];
                        })(),
                        createdAt: DateTimeImmutable::createFromFormat(
                            'Y-m-d H:i:s',
                            '2000-01-01 00:00:00',
                            new DateTimeZone('America/Sao_Paulo')
                        )
                    )
                ],
                'expected' => '[{"id":"2c485713-521c-4d91-b9e7-1294f132ad2e","items":[{"name":"Macbook Pro"},{"name":"iPhone XYZ"}],"createdAt":"2000-01-01T00:00:00-02:00"}]'
            ],
            'Scalar collection'                       => [
                'elements' => ['iPhone', PHP_INT_MAX, 123.456, ['nested' => PHP_INT_MAX], PHP_INT_MIN],
                'expected' => '["iPhone", 9223372036854775807, 123.456, {"nested":9223372036854775807}, -9223372036854775808]'
            ],
            'Dragon collection'                       => [
                'elements' => [
                    new Dragon(
                        name: 'Ignithar Blazeheart',
                        type: DragonType::FIRE,
                        power: 10000000.00,
                        skills: DragonSkills::cases()
                    )
                ],
                'expected' => '[{"name":"Ignithar Blazeheart","type":"FIRE","power":10000000,"skills":["fly","spell","regeneration","elemental_breath"]}]'
            ],
            'Decimal collection'                      => [
                'elements' => [
                    new Decimal(value: 100.00),
                    new Decimal(value: 123.45),
                    new Decimal(value: 999.99),
                ],
                'expected' => '[{"value":100},{"value":123.45},{"value":999.99}]'
            ],
            'Product collection'                      => [
                'elements' => [
                    new Product(
                        name: 'Macbook Pro',
                        amount: Amount::from(value: 1600.00, currency: Currency::USD),
                        stockBatch: new ArrayIterator([1000, 2000, 3000])
                    )
                ],
                'expected' => '[{"name":"Macbook Pro","amount":{"value":1600,"currency":"USD"},"stockBatch":[1000,2000,3000]}]'
            ],
            'Expiration date collection'              => [
                'elements' => [
                    new ExpirationDate(
                        value: new DateTimeImmutable(
                            '2000-01-01 00:00:00',
                            new DateTimeZone('UTC')
                        )
                    )
                ],
                'expected' => '[{"value": "2000-01-01 00:00:00"}]'
            ],
            'Shipping object with no addresses'       => [
                'elements' => [
                    new Shipping(id: PHP_INT_MAX, addresses: new ShippingAddresses())
                ],
                'expected' => '[{"id":9223372036854775807,"addresses":[]}]'
            ],
            'Shipping object with a single address'   => [
                'elements' => [
                    new Shipping(
                        id: PHP_INT_MIN,
                        addresses: new ShippingAddresses(
                            elements: [
                                new ShippingAddress(
                                    city: 'São Paulo',
                                    state: ShippingState::SP,
                                    street: 'Avenida Paulista',
                                    number: 100,
                                    country: ShippingCountry::BRAZIL
                                )
                            ]
                        )
                    )
                ],
                'expected' => '[{"id":-9223372036854775808,"addresses":[{"city":"São Paulo","state":"SP","street":"Avenida Paulista","number":100,"country":"BR"}]}]'
            ],
            'Shipping object with multiple addresses' => [
                'elements' => [
                    new Shipping(
                        id: 100000,
                        addresses: new ShippingAddresses(
                            elements: [
                                new ShippingAddress(
                                    city: 'New York',
                                    state: ShippingState::NY,
                                    street: '5th Avenue',
                                    number: 1,
                                    country: ShippingCountry::UNITED_STATES
                                ),
                                new ShippingAddress(
                                    city: 'New York',
                                    state: ShippingState::NY,
                                    street: 'Broadway',
                                    number: 42,
                                    country: ShippingCountry::UNITED_STATES
                                )
                            ]
                        )
                    )
                ],
                'expected' => '[{"id":100000,"addresses":[{"city":"New York","state":"NY","street":"5th Avenue","number":1,"country":"US"},{"city":"New York","state":"NY","street":"Broadway","number":42,"country":"US"}]}]'
            ]
        ];
    }

    public static function dataProviderForToArray(): iterable
    {
        return [
            'Order collection'                        => [
                'elements' => [
                    new Order(
                        id: '2c485713-521c-4d91-b9e7-1294f132ad2e',
                        items: (static function () {
                            yield ['name' => 'Macbook Pro'];
                            yield ['name' => 'iPhone XYZ'];
                        })(),
                        createdAt: DateTimeImmutable::createFromFormat(
                            'Y-m-d H:i:s',
                            '2000-01-01 00:00:00',
                            new DateTimeZone('America/Sao_Paulo')
                        )
                    )
                ],
                'expected' => [
                    [
                        'id'        => '2c485713-521c-4d91-b9e7-1294f132ad2e',
                        'items'     => [['name' => 'Macbook Pro'], ['name' => 'iPhone XYZ']],
                        'createdAt' => '2000-01-01T00:00:00-02:00'
                    ]
                ]
            ],
            'Dragon collection'                       => [
                'elements' => [
                    new Dragon(
                        name: 'Ignithar Blazeheart',
                        type: DragonType::FIRE,
                        power: 10000000.00,
                        skills: DragonSkills::cases()
                    )
                ],
                'expected' => [
                    [
                        'name'   => 'Ignithar Blazeheart',
                        'type'   => 'FIRE',
                        'power'  => 10000000.00,
                        'skills' => ['fly', 'spell', 'regeneration', 'elemental_breath']
                    ]
                ]
            ],
            'Decimal collection'                      => [
                'elements' => [
                    new Decimal(value: 100.00),
                    new Decimal(value: 123.45),
                    new Decimal(value: 999.99),
                ],
                'expected' => [
                    ['value' => 100.00],
                    ['value' => 123.45],
                    ['value' => 999.99]
                ]
            ],
            'Product collection'                      => [
                'elements' => [
                    new Product(
                        name: 'Macbook Pro',
                        amount: Amount::from(value: 1600.00, currency: Currency::USD),
                        stockBatch: new ArrayIterator([1000, 2000, 3000])
                    )
                ],
                'expected' => [
                    [
                        'name'       => 'Macbook Pro',
                        'amount'     => ['value' => 1600.00, 'currency' => Currency::USD->value],
                        'stockBatch' => [1000, 2000, 3000]
                    ]
                ]
            ],
            'Expiration date collection'              => [
                'elements' => [
                    new ExpirationDate(
                        value: new DateTimeImmutable(
                            '2000-01-01 00:00:00',
                            new DateTimeZone('UTC')
                        )
                    )
                ],
                'expected' => [['value' => '2000-01-01 00:00:00']]
            ],
            'Shipping object with no addresses'       => [
                'elements' => [
                    new Shipping(id: PHP_INT_MAX, addresses: new ShippingAddresses())
                ],
                'expected' => [
                    [
                        'id'        => PHP_INT_MAX,
                        'addresses' => []
                    ]
                ]
            ],
            'Shipping object with a single address'   => [
                'elements' => [
                    new Shipping(
                        id: PHP_INT_MIN,
                        addresses: new ShippingAddresses(
                            elements: [
                                new ShippingAddress(
                                    city: 'São Paulo',
                                    state: ShippingState::SP,
                                    street: 'Avenida Paulista',
                                    number: 100,
                                    country: ShippingCountry::BRAZIL
                                )
                            ]
                        )
                    )
                ],
                'expected' => [
                    [
                        'id'        => PHP_INT_MIN,
                        'addresses' => [
                            [
                                'city'    => 'São Paulo',
                                'state'   => ShippingState::SP->name,
                                'street'  => 'Avenida Paulista',
                                'number'  => 100,
                                'country' => ShippingCountry::BRAZIL->value
                            ]
                        ]
                    ]
                ]
            ],
            'Shipping object with multiple addresses' => [
                'elements' => [
                    new Shipping(
                        id: 100000,
                        addresses: new ShippingAddresses(
                            elements: [
                                new ShippingAddress(
                                    city: 'New York',
                                    state: ShippingState::NY,
                                    street: '5th Avenue',
                                    number: 1,
                                    country: ShippingCountry::UNITED_STATES
                                ),
                                new ShippingAddress(
                                    city: 'New York',
                                    state: ShippingState::NY,
                                    street: 'Broadway',
                                    number: 42,
                                    country: ShippingCountry::UNITED_STATES
                                )
                            ]
                        )
                    )
                ],
                'expected' => [
                    [
                        'id'        => 100000,
                        'addresses' => [
                            [
                                'city'    => 'New York',
                                'state'   => ShippingState::NY->name,
                                'street'  => '5th Avenue',
                                'number'  => 1,
                                'country' => ShippingCountry::UNITED_STATES->value
                            ],
                            [
                                'city'    => 'New York',
                                'state'   => ShippingState::NY->name,
                                'street'  => 'Broadway',
                                'number'  => 42,
                                'country' => ShippingCountry::UNITED_STATES->value
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public static function dataProviderForToJsonDiscardKeys(): iterable
    {
        return [
            'Scalar collection'        => [
                'elements' => ['float' => 12.34, 'string' => 'apple', 'integer' => 100, 'boolean' => true],
                'expected' => '[12.34,"apple",100,true]'
            ],
            'ArrayIterator collection' => [
                'elements' => new ArrayIterator([
                    'float'   => 12.34,
                    'string'  => 'apple',
                    'integer' => 100,
                    'boolean' => true
                ]),
                'expected' => '[12.34,"apple",100,true]'
            ]
        ];
    }

    public static function dataProviderForToJsonPreserveKeys(): iterable
    {
        return [
            'Scalar collection'        => [
                'elements' => ['float' => 12.34, 'string' => 'apple', 'integer' => 100, 'boolean' => true],
                'expected' => '{"float":12.34,"string":"apple","integer":100,"boolean":true}'
            ],
            'ArrayIterator collection' => [
                'elements' => new ArrayIterator([
                    'float'   => 12.34,
                    'string'  => 'apple',
                    'integer' => 100,
                    'boolean' => true
                ]),
                'expected' => '{"float":12.34,"string":"apple","integer":100,"boolean":true}'
            ]
        ];
    }

    public static function dataProviderForToArrayDiscardKeys(): iterable
    {
        return [
            'Scalar collection'        => [
                'elements' => ['float' => 12.34, 'string' => 'apple', 'integer' => 100, 'boolean' => true],
                'expected' => [12.34, 'apple', 100, true]
            ],
            'ArrayIterator collection' => [
                'elements' => new ArrayIterator([
                    'float'   => 12.34,
                    'string'  => 'apple',
                    'integer' => 100,
                    'boolean' => true
                ]),
                'expected' => [12.34, 'apple', 100, true]
            ]
        ];
    }

    public static function dataProviderForToArrayPreserveKeys(): iterable
    {
        return [
            'Scalar collection'        => [
                'elements' => ['float' => 12.34, 'string' => 'apple', 'integer' => 100, 'boolean' => true],
                'expected' => ['float' => 12.34, 'string' => 'apple', 'integer' => 100, 'boolean' => true]
            ],
            'ArrayIterator collection' => [
                'elements' => new ArrayIterator([
                    'float'   => 12.34,
                    'string'  => 'apple',
                    'integer' => 100,
                    'boolean' => true
                ]),
                'expected' => ['float' => 12.34, 'string' => 'apple', 'integer' => 100, 'boolean' => true]
            ]
        ];
    }
}
