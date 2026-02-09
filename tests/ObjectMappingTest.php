<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper;

use ArrayIterator;
use DateTimeImmutable;
use DateTimeZone;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Amount;
use Test\TinyBlocks\Mapper\Models\Color;
use Test\TinyBlocks\Mapper\Models\Currency;
use Test\TinyBlocks\Mapper\Models\Description;
use Test\TinyBlocks\Mapper\Models\Member;
use Test\TinyBlocks\Mapper\Models\MemberId;
use Test\TinyBlocks\Mapper\Models\Members;
use Test\TinyBlocks\Mapper\Models\Order;
use Test\TinyBlocks\Mapper\Models\Organization;
use Test\TinyBlocks\Mapper\Models\OrganizationId;
use Test\TinyBlocks\Mapper\Models\Product;
use Test\TinyBlocks\Mapper\Models\ProductStatus;
use Test\TinyBlocks\Mapper\Models\Service;
use Test\TinyBlocks\Mapper\Models\Tag;
use Test\TinyBlocks\Mapper\Models\Uuid;
use Test\TinyBlocks\Mapper\Models\Webhook;
use TinyBlocks\Mapper\ObjectMapper;

final class ObjectMappingTest extends TestCase
{
    #[DataProvider('objectProvider')]
    public function testObject(ObjectMapper $object, array $expected): void
    {
        /** @Given an Object */
        /** @When mapping the object to an array */
        $actual = $object->toArray();

        /** @Then the mapped array should have expected values */
        self::assertSame($expected, $actual);

        /** @And the JSON representation should be the mapped JSON object */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $object->toJson());
    }

    public function testObjectWithClosure(): void
    {
        /** @Given a Service with a Closure property */
        $service = new Service(action: static fn() => 'executed');

        /** @When mapping the Service to an array */
        $actual = $service->toArray();

        /** @Then the mapped array should have expected values */
        $expected = ['action' => []];

        self::assertSame($expected, $actual);

        /** @And the JSON representation should be the mapped JSON object */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $service->toJson());
    }

    public function testObjectWithGenerator(): void
    {
        /** @Given an Order with a Generator of items */
        $order = new Order(
            id: new Uuid(value: '123e4567-e89b-12d3-a456-426614174000'),
            items: (function (): Generator {
                yield ['sku' => 'ITEM-001', 'quantity' => 2];
                yield ['sku' => 'ITEM-002', 'quantity' => 1];
                yield ['sku' => 'ITEM-003', 'quantity' => 5];
            })(),
            createdAt: new DateTimeImmutable('2025-01-01 10:00:00', new DateTimeZone('UTC'))
        );

        /** @When mapping the Order to an array */
        $actual = $order->toArray();

        /** @Then the mapped array should have expected values */
        $expected = [
            'id'        => '123e4567-e89b-12d3-a456-426614174000',
            'items'     => [
                ['sku' => 'ITEM-001', 'quantity' => 2],
                ['sku' => 'ITEM-002', 'quantity' => 1],
                ['sku' => 'ITEM-003', 'quantity' => 5]
            ],
            'createdAt' => '2025-01-01T10:00:00+00:00'
        ];

        self::assertSame($expected, $actual);

        /** @And the JSON representation should be the mapped JSON object */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), json_encode($actual));
    }

    public function testObjectWithoutConstructor(): void
    {
        /** @Given a Webhook with no constructor */
        $webhook = new Webhook();
        $webhook->url = 'https://example.com/hook';
        $webhook->active = true;

        /** @When mapping the Webhook to an array */
        $actual = $webhook->toArray();

        /** @Then the mapped array should have expected values */
        $expected = [
            'url'    => 'https://example.com/hook',
            'active' => true
        ];

        self::assertSame($expected, $actual);

        /** @And the JSON representation should be the mapped JSON object */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $webhook->toJson());
    }

    public function testObjectWithStaticProperties(): void
    {
        /** @Given a Webhook with no constructor and static properties */
        $webhook = new Webhook();
        $webhook->url = 'https://example.com/static-test';
        $webhook->active = true;
        Webhook::$timeout = 60;

        /** @When mapping the Webhook to an array */
        $actual = $webhook->toArray();

        /** @Then the mapped array should have expected values */
        $expected = [
            'url'    => 'https://example.com/static-test',
            'active' => true
        ];

        self::assertSame($expected, $actual);

        /** @And the JSON representation should be the mapped JSON object */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $webhook->toJson());
    }

    public function testObjectWithoutConstructorWithDefaultValues(): void
    {
        /** @Given a Webhook with no constructor using default property values */
        $webhook = new Webhook();

        /** @When mapping the Webhook to an array */
        $actual = $webhook->toArray();

        /** @Then the mapped array should have expected default values */
        $expected = [
            'url'    => '',
            'active' => false
        ];

        self::assertSame($expected, $actual);

        /** @And the JSON representation should be the mapped JSON object */
        self::assertJsonStringEqualsJsonString((string)json_encode($expected), $webhook->toJson());
    }

    public static function objectProvider(): array
    {
        return [
            'Tag object'          => [
                'object'   => new Tag(),
                'expected' => ['name' => '', 'color' => 'gray']
            ],
            'Service object'      => [
                'object'   => Service::fromIterable(iterable: ['action' => static fn() => 'executed']),
                'expected' => ['action' => []]
            ],
            'Product object'      => [
                'object'   => new Product(
                    id: 1,
                    amount: Amount::from(value: 49.90, currency: Currency::USD),
                    description: new Description(text: 'Wireless Mouse'),
                    attributes: new ArrayIterator([
                        'color'    => Color::BLUE,
                        'weight'   => 0.12,
                        'wireless' => true
                    ]),
                    inventory: [10, 25, 50],
                    status: ProductStatus::ACTIVE,
                    createdAt: new DateTimeImmutable('2026-01-15T08:30:00+00:00')
                ),
                'expected' => [
                    'id'          => 1,
                    'amount'      => [
                        'value'    => 49.90,
                        'currency' => 'USD'
                    ],
                    'description' => 'Wireless Mouse',
                    'attributes'  => [
                        'color'    => 'blue',
                        'weight'   => 0.12,
                        'wireless' => true
                    ],
                    'inventory'   => [10, 25, 50],
                    'status'      => 1,
                    'createdAt'   => '2026-01-15T08:30:00+00:00'
                ]
            ],
            'Organization object' => [
                'object'   => new Organization(
                    id: new OrganizationId(value: new Uuid(value: 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23')),
                    name: 'Tech Corp',
                    members: Members::createFrom(elements: [
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
                    invitations: []
                ),
                'expected' => [
                    'id'          => 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23',
                    'name'        => 'Tech Corp',
                    'members'     => [
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
                    ],
                    'invitations' => []
                ]
            ]
        ];
    }
}
