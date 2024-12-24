<?php

declare(strict_types=1);

namespace TinyBlocks\Mapper;

use ArrayIterator;
use DateTimeImmutable;
use DateTimeZone;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Mapper\Internal\Exceptions\InvalidCast;
use TinyBlocks\Mapper\Models\Amount;
use TinyBlocks\Mapper\Models\Configuration;
use TinyBlocks\Mapper\Models\Currency;
use TinyBlocks\Mapper\Models\Decimal;
use TinyBlocks\Mapper\Models\Dragon;
use TinyBlocks\Mapper\Models\DragonSkills;
use TinyBlocks\Mapper\Models\DragonType;
use TinyBlocks\Mapper\Models\ExpirationDate;
use TinyBlocks\Mapper\Models\Order;
use TinyBlocks\Mapper\Models\Product;
use TinyBlocks\Mapper\Models\Service;
use TinyBlocks\Mapper\Models\Shipping;
use TinyBlocks\Mapper\Models\ShippingAddress;
use TinyBlocks\Mapper\Models\ShippingAddresses;
use TinyBlocks\Mapper\Models\ShippingCountry;
use TinyBlocks\Mapper\Models\ShippingState;

final class ObjectMapperTest extends TestCase
{
    #[DataProvider('dataProviderForToJson')]
    public function testObjectToJson(ObjectMapper $object, string $expected): void
    {
        /** @Given an object with values */
        /** @When converting the object to JSON */
        $actual = $object->toJson();

        /** @Then the result should match the expected */
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    #[DataProvider('dataProviderForToArray')]
    public function testObjectToArray(ObjectMapper $object, iterable $expected): void
    {
        /** @Given an object with values */
        /** @When converting the object to array */
        $actual = $object->toArray();

        /** @Then the result should match the expected */
        self::assertSame($expected, $actual);
    }

    #[DataProvider('dataProviderForToJsonDiscardKeys')]
    public function testObjectToJsonDiscardKeys(ObjectMapper $object, string $expected): void
    {
        /** @Given an object with values */
        /** @When converting the object to JSON while discarding keys */
        $actual = $object->toJson(keyPreservation: KeyPreservation::DISCARD);

        /** @Then the result should match the expected */
        self::assertSame($expected, $actual);
    }

    #[DataProvider('dataProviderForToArrayDiscardKeys')]
    public function testObjectToArrayDiscardKeys(ObjectMapper $object, iterable $expected): void
    {
        /** @Given an object with values */
        /** @When converting the object to array while discarding keys */
        $actual = $object->toArray(keyPreservation: KeyPreservation::DISCARD);

        /** @Then the result should match the expected */
        self::assertSame($expected, $actual);
    }

    #[DataProvider('dataProviderForIterableToObject')]
    public function testIterableToObject(iterable $iterable, ObjectMapper $expected): void
    {
        /** @Given an iterable with values */
        /** @When converting the array to object */
        $actual = $expected::fromIterable(iterable: $iterable);

        /** @Then the result should match the expected */
        self::assertSame($expected->toArray(), $actual->toArray());
        self::assertEquals($expected, $actual);
        self::assertNotSame($expected, $actual);
    }

    public function testInvalidObjectValueToArrayReturnsEmptyArray(): void
    {
        /** @Given an object with an invalid item (e.g., a function that cannot be serialized) */
        $service = new Service(action: fn(): int => 0);

        /** @When attempting to serialize the object containing the invalid item */
        $actual = $service->toJson();

        /** @Then the invalid item should be serialized as an empty array in the JSON output */
        self::assertSame('[]', $actual);
    }

    public function testExceptionWhenInvalidCast(): void
    {
        /** @Given an iterable with invalid values */
        $iterable = ['value' => 100.50, 'currency' => 'EUR'];

        /** @Then a InvalidCast exception should be thrown */
        self::expectException(InvalidCast::class);
        self::expectExceptionMessage('Invalid value <EUR> for enum <TinyBlocks\Mapper\Models\Currency>.');

        /** @When the fromIterable method is called on the object */
        Amount::fromIterable(iterable: $iterable);
    }

    public static function dataProviderForToJson(): iterable
    {
        return [
            'Order object'                            => [
                'object'   => new Order(
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
                ),
                'expected' => '{"id":"2c485713-521c-4d91-b9e7-1294f132ad2e","items":[{"name":"Macbook Pro"},{"name":"iPhone XYZ"}],"createdAt":"2000-01-01T00:00:00-02:00"}'
            ],
            'Dragon object'                           => [
                'object'   => new Dragon(
                    name: 'Ignithar Blazeheart',
                    type: DragonType::FIRE,
                    power: 10000000.00,
                    skills: DragonSkills::cases()
                ),
                'expected' => '{"name":"Ignithar Blazeheart","type":"FIRE","power":10000000,"skills":["fly","spell","regeneration","elemental_breath"]}'
            ],
            'Decimal object'                          => [
                'object'   => new Decimal(value: 999.99),
                'expected' => '{"value":999.99}'
            ],
            'Product object'                          => [
                'object'   => new Product(
                    name: 'Macbook Pro',
                    amount: Amount::from(value: 1600.00, currency: Currency::USD),
                    stockBatch: new ArrayIterator([1000, 2000, 3000])
                ),
                'expected' => '{"name":"Macbook Pro","amount":{"value":1600,"currency":"USD"},"stockBatch":[1000,2000,3000]}'
            ],
            'ExpirationDate object'                   => [
                'object'   => new ExpirationDate(
                    value: new DateTimeImmutable(
                        '2000-01-01 00:00:00',
                        new DateTimeZone('UTC')
                    )
                ),
                'expected' => '{"value":"2000-01-01 00:00:00"}'
            ],
            'Shipping object with no addresses'       => [
                'object'   => new Shipping(id: PHP_INT_MAX, addresses: new ShippingAddresses()),
                'expected' => '{"id": 9223372036854775807,"addresses":[]}'
            ],
            'Shipping object with a single address'   => [
                'object'   => new Shipping(
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
                ),
                'expected' => '{"id": -9223372036854775808,"addresses":[{"city":"São Paulo","state":"SP","street":"Avenida Paulista","number":100,"country":"BR"}]}'
            ],
            'Shipping object with multiple addresses' => [
                'object'   => new Shipping(
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
                ),
                'expected' => '{"id":100000,"addresses":[{"city":"New York","state":"NY","street":"5th Avenue","number":1,"country":"US"},{"city":"New York","state":"NY","street":"Broadway","number":42,"country":"US"}]}'
            ]
        ];
    }

    public static function dataProviderForToArray(): iterable
    {
        return [
            'Order object'                            => [
                'object'   => new Order(
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
                ),
                'expected' => [
                    'id'        => '2c485713-521c-4d91-b9e7-1294f132ad2e',
                    'items'     => [['name' => 'Macbook Pro'], ['name' => 'iPhone XYZ']],
                    'createdAt' => '2000-01-01T00:00:00-02:00'
                ]
            ],
            'Dragon object'                           => [
                'object'   => new Dragon(
                    name: 'Ignithar Blazeheart',
                    type: DragonType::FIRE,
                    power: 10000000.00,
                    skills: DragonSkills::cases()
                ),
                'expected' => [
                    'name'   => 'Ignithar Blazeheart',
                    'type'   => 'FIRE',
                    'power'  => 10000000.00,
                    'skills' => ['fly', 'spell', 'regeneration', 'elemental_breath']
                ]
            ],
            'Decimal object'                          => [
                'object'   => new Decimal(value: 999.99),
                'expected' => ['value' => 999.99]
            ],
            'Product object'                          => [
                'object'   => new Product(
                    name: 'Macbook Pro',
                    amount: Amount::from(value: 1600.00, currency: Currency::USD),
                    stockBatch: new ArrayIterator([1000, 2000, 3000])
                ),
                'expected' => [
                    'name'       => 'Macbook Pro',
                    'amount'     => ['value' => 1600.00, 'currency' => Currency::USD->value],
                    'stockBatch' => [1000, 2000, 3000]
                ]
            ],
            'ExpirationDate object'                   => [
                'object'   => new ExpirationDate(
                    value: new DateTimeImmutable(
                        '2000-01-01 00:00:00',
                        new DateTimeZone('UTC')
                    )
                ),
                'expected' => ['value' => '2000-01-01 00:00:00']
            ],
            'Shipping object with no addresses'       => [
                'object'   => new Shipping(id: PHP_INT_MAX, addresses: new ShippingAddresses()),
                'expected' => [
                    'id'        => PHP_INT_MAX,
                    'addresses' => []
                ]
            ],
            'Shipping object with a single address'   => [
                'object'   => new Shipping(
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
                ),
                'expected' => [
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
            ],
            'Shipping object with multiple addresses' => [
                'object'   => new Shipping(
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
                ),
                'expected' => [
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
        ];
    }

    public static function dataProviderForIterableToObject(): iterable
    {
        return [
            'Order object'                            => [
                'iterable' => [
                    'id'        => '2c485713-521c-4d91-b9e7-1294f132ad2e',
                    'items'     => [['name' => 'Macbook Pro'], ['name' => 'iPhone XYZ']],
                    'createdAt' => '2000-01-01T00:00:00-02:00'
                ],
                'expected' => new Order(
                    id: '2c485713-521c-4d91-b9e7-1294f132ad2e',
                    items: (static function (): Generator {
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
            'Amount object'                           => [
                'iterable' => ['value' => 999.99, 'currency' => 'USD'],
                'expected' => Amount::from(value: 999.99, currency: Currency::USD)
            ],
            'Decimal object'                          => [
                'iterable' => ['value' => 999.99],
                'expected' => new Decimal(value: 999.99)
            ],
            'Product object'                          => [
                'iterable' => [
                    'name'       => 'Macbook Pro',
                    'amount'     => ['value' => 1600.00, 'currency' => 'USD'],
                    'stockBatch' => [1000, 2000, 3000]
                ],
                'expected' => new Product(
                    name: 'Macbook Pro',
                    amount: Amount::from(value: 1600.00, currency: Currency::USD),
                    stockBatch: new ArrayIterator([1000, 2000, 3000])
                )
            ],
            'Configuration object'                    => [
                'iterable' => [
                    'id'      => PHP_INT_MAX,
                    'options' => ['ON', 'OFF']
                ],
                'expected' => new Configuration(
                    id: (static function (): Generator {
                        yield PHP_INT_MAX;
                    })(),
                    options: (static function (): Generator {
                        yield 'ON';
                        yield 'OFF';
                    })()
                )
            ],
            'Shipping object with no addresses'       => [
                'iterable' => ['id' => PHP_INT_MAX, 'addresses' => []],
                'expected' => new Shipping(id: PHP_INT_MAX, addresses: new ShippingAddresses())
            ],
            'Shipping object with a single address'   => [
                'iterable' => [
                    'id'        => PHP_INT_MIN,
                    'addresses' => [
                        [
                            'city'    => 'São Paulo',
                            'state'   => 'SP',
                            'street'  => 'Avenida Paulista',
                            'number'  => 100,
                            'country' => 'BR'
                        ]
                    ]
                ],
                'expected' => new Shipping(
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
            'Shipping object with multiple addresses' => [
                'iterable' => [
                    'id'        => PHP_INT_MIN,
                    'addresses' => [
                        [
                            'city'    => 'New York',
                            'state'   => 'NY',
                            'street'  => '5th Avenue',
                            'number'  => 717,
                            'country' => 'US'
                        ],
                        [
                            'city'    => 'New York',
                            'state'   => 'NY',
                            'street'  => 'Broadway',
                            'number'  => 42,
                            'country' => 'US'
                        ]
                    ]
                ],
                'expected' => new Shipping(
                    id: PHP_INT_MIN,
                    addresses: new ShippingAddresses(
                        elements: [
                            new ShippingAddress(
                                city: 'New York',
                                state: ShippingState::NY,
                                street: '5th Avenue',
                                number: 717,
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
            ]
        ];
    }

    public static function dataProviderForToJsonDiscardKeys(): iterable
    {
        return [
            'Amount object'                         => [
                'object'   => Amount::from(value: 999.99, currency: Currency::USD),
                'expected' => '[999.99,"USD"]'
            ],
            'Shipping object with a single address' => [
                'object'   => new Shipping(
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
                ),
                'expected' => '[-9223372036854775808,[["São Paulo","SP","Avenida Paulista",100,"BR"]]]'
            ]
        ];
    }

    public static function dataProviderForToArrayDiscardKeys(): iterable
    {
        return [
            'Amount object'                         => [
                'object'   => Amount::from(value: 999.99, currency: Currency::USD),
                'expected' => [999.99, 'USD']
            ],
            'Shipping object with a single address' => [
                'object'   => new Shipping(
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
                ),
                'expected' => [
                    PHP_INT_MIN,
                    [
                        [
                            'São Paulo',
                            ShippingState::SP->name,
                            'Avenida Paulista',
                            100,
                            ShippingCountry::BRAZIL->value
                        ]
                    ]
                ]
            ]
        ];
    }
}
