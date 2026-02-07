<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\DeepValue;
use Test\TinyBlocks\Mapper\Models\Member;
use Test\TinyBlocks\Mapper\Models\MemberId;
use Test\TinyBlocks\Mapper\Models\Members;
use Test\TinyBlocks\Mapper\Models\Organization;
use Test\TinyBlocks\Mapper\Models\OrganizationId;
use Test\TinyBlocks\Mapper\Models\UserId;
use Test\TinyBlocks\Mapper\Models\Uuid;

final class ValueObjectMappingTest extends TestCase
{
    #[DataProvider('dataProviderForValueObjectUnwrapping')]
    public function testValueObjectsAreUnwrappedToScalars(Organization $organization, array $expected): void
    {
        /** @Given an organization with deeply nested Value Objects */
        /** @When converting the organization to array */
        $actual = $organization->toArray();

        /** @Then all Value Objects should be unwrapped to their scalar values */
        self::assertSame($expected, $actual);
    }

    #[DataProvider('dataProviderForValueObjectUnwrapping')]
    public function testValueObjectsAreUnwrappedInJson(Organization $organization, array $expected): void
    {
        /** @Given an organization with deeply nested Value Objects */
        $expected = json_encode($expected, JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE);

        /** @When converting the organization to JSON */
        $actual = $organization->toJson();

        /** @Then all Value Objects should be unwrapped to their scalar values in JSON */
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    #[DataProvider('dataProviderForFromIterable')]
    public function testCreateOrganizationFromIterable(array $data, Organization $expected): void
    {
        /** @Given an array with organization data containing Value Object structures */
        /** @When creating an organization from the iterable */
        $actual = Organization::fromIterable(iterable: $data);

        /** @Then the organization should be created with all Value Objects properly instantiated */
        self::assertEquals($expected->toArray(), $actual->toArray());
    }

    public function testSingleLevelUnwrapping(): void
    {
        /** @Given a Value Object with one level of nesting */
        $userId = new UserId(value: new Uuid(value: '88f15d3f-c9b9-4855-9778-5ba7926b6736'));

        /** @When converting to array */
        $actual = $userId->toArray();

        /** @Then the Value Object should be unwrapped to its scalar value */
        self::assertSame(['value' => '88f15d3f-c9b9-4855-9778-5ba7926b6736'], $actual);
    }

    public function testDoubleLevelUnwrapping(): void
    {
        /** @Given a Value Object with two levels of nesting */
        $organizationId = new OrganizationId(value: new Uuid(value: '88f15d3f-c9b9-4855-9778-5ba7926b6736'));

        /** @When converting to array */
        $actual = $organizationId->toArray();

        /** @Then the Value Object should be unwrapped through both levels */
        self::assertSame(['value' => '88f15d3f-c9b9-4855-9778-5ba7926b6736'], $actual);
    }

    public function testDeeplyNestedUnwrapping(): void
    {
        /** @Given a Value Object nested 15 levels deep */
        $current = new DeepValue(value: 'scalar-at-bottom');

        for ($i = 0; $i < 14; $i++) {
            $current = new DeepValue(value: $current);
        }

        /** @When converting to array */
        $actual = $current->toArray();

        /** @Then all levels should be unwrapped to the scalar */
        self::assertSame(['value' => 'scalar-at-bottom'], $actual);
    }

    public function testValueObjectWrappingArray(): void
    {
        /** @Given a Value Object wrapping another Value Object whose value is an array */
        $inner = new DeepValue(value: ['a', 'b', 'c']);
        $outer = new DeepValue(value: $inner);

        /** @When converting to array */
        $actual = $outer->toArray();

        /** @Then both levels should be unwrapped to the array */
        self::assertSame(['value' => ['a', 'b', 'c']], $actual);
    }

    public function testValueObjectWithNullValue(): void
    {
        /** @Given a Value Object whose value is null */
        $deepValue = new DeepValue(value: null);

        /** @When converting to array */
        $actual = $deepValue->toArray();

        /** @Then null should be unwrapped from the Value Object */
        self::assertSame(['value' => null], $actual);
    }

    public function testNestedValueObjectWithNullAtBottom(): void
    {
        /** @Given a nested Value Object with null at the deepest level */
        $inner = new DeepValue(value: null);
        $outer = new DeepValue(value: $inner);

        /** @When converting to array */
        $actual = $outer->toArray();

        /** @Then null should be unwrapped through all levels */
        self::assertSame(['value' => null], $actual);
    }

    public function testComplexObjectWithMultipleValueObjects(): void
    {
        /** @Given a complex object with multiple deeply nested Value Objects */
        $member = new Member(
            id: new MemberId(value: new Uuid(value: 'member-uuid')),
            role: 'admin',
            userId: new UserId(value: new Uuid(value: 'user-uuid')),
            isOwner: true,
            organizationId: new OrganizationId(value: new Uuid(value: 'org-uuid'))
        );

        /** @When converting to array */
        $actual = $member->toArray();

        /** @Then all Value Objects should be unwrapped to scalars */
        self::assertSame([
            'id'             => 'member-uuid',
            'role'           => 'admin',
            'userId'         => 'user-uuid',
            'isOwner'        => true,
            'organizationId' => 'org-uuid'
        ], $actual);
    }

    public function testCollectionWithValueObjects(): void
    {
        /** @Given a collection of members with nested Value Objects */
        $members = Members::createFrom(elements: [
            new Member(
                id: new MemberId(value: new Uuid(value: '88f15d3f-c9b9-4855-9778-5ba7926b6736')),
                role: 'admin',
                userId: new UserId(value: new Uuid(value: '4a12fa11-33d1-4ac1-bc15-90af7dbee0c8')),
                isOwner: true,
                organizationId: new OrganizationId(value: new Uuid(value: 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a'))
            ),
            new Member(
                id: new MemberId(value: new Uuid(value: 'c23b4c0a-f6d1-4b02-af2a-28b120a0ceb6')),
                role: 'viewer',
                userId: new UserId(value: new Uuid(value: 'b2c98bc8-c3f2-451b-a476-c4ec6ae23036')),
                isOwner: false,
                organizationId: new OrganizationId(value: new Uuid(value: 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a'))
            )
        ]);

        /** @When converting the collection to array */
        $actual = $members->toArray();

        /** @Then all nested Value Objects should be unwrapped */
        self::assertSame([
            [
                'id'             => '88f15d3f-c9b9-4855-9778-5ba7926b6736',
                'role'           => 'admin',
                'userId'         => '4a12fa11-33d1-4ac1-bc15-90af7dbee0c8',
                'isOwner'        => true,
                'organizationId' => 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a'
            ],
            [
                'id'             => 'c23b4c0a-f6d1-4b02-af2a-28b120a0ceb6',
                'role'           => 'viewer',
                'userId'         => 'b2c98bc8-c3f2-451b-a476-c4ec6ae23036',
                'isOwner'        => false,
                'organizationId' => 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a'
            ]
        ], $actual);
    }

    public function testKeyPreservationWithValueObjects(): void
    {
        /** @Given an organization with Value Objects */
        $organization = new Organization(
            id: new OrganizationId(value: new Uuid(value: 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23')),
            name: 'Test Org',
            members: Members::createFromEmpty(),
            invitations: []
        );

        /** @When converting to array with PRESERVE keys */
        $actual = $organization->toArray();

        /** @Then all keys should be preserved */
        self::assertArrayHasKey('id', $actual);
        self::assertArrayHasKey('name', $actual);
        self::assertArrayHasKey('members', $actual);
        self::assertArrayHasKey('invitations', $actual);
    }

    public static function dataProviderForValueObjectUnwrapping(): iterable
    {
        return [
            'Organization with no members'       => [
                'organization' => new Organization(
                    id: new OrganizationId(value: new Uuid(value: 'empty-org')),
                    name: 'Empty Org',
                    members: Members::createFromEmpty(),
                    invitations: []
                ),
                'expected'     => [
                    'id'          => 'empty-org',
                    'name'        => 'Empty Org',
                    'members'     => [],
                    'invitations' => []
                ]
            ],
            'Organization with single member'    => [
                'organization' => new Organization(
                    id: new OrganizationId(value: new Uuid(value: '6daca0fb-f718-414d-bdb8-5d1b2d65628b')),
                    name: 'Calenvo',
                    members: Members::createFrom(elements: [
                        new Member(
                            id: new MemberId(value: new Uuid(value: '08a6ce33-95e7-43db-b566-9620216cdd5a')),
                            role: 'admin',
                            userId: new UserId(value: new Uuid(value: '2e9f9b9b-febb-4c01-a7f7-f802c2e712d2')),
                            isOwner: true,
                            organizationId: new OrganizationId(
                                value: new Uuid(value: '6daca0fb-f718-414d-bdb8-5d1b2d65628b')
                            )
                        )
                    ]),
                    invitations: []
                ),
                'expected'     => [
                    'id'          => '6daca0fb-f718-414d-bdb8-5d1b2d65628b',
                    'name'        => 'Calenvo',
                    'members'     => [
                        [
                            'id'             => '08a6ce33-95e7-43db-b566-9620216cdd5a',
                            'role'           => 'admin',
                            'userId'         => '2e9f9b9b-febb-4c01-a7f7-f802c2e712d2',
                            'isOwner'        => true,
                            'organizationId' => '6daca0fb-f718-414d-bdb8-5d1b2d65628b'
                        ]
                    ],
                    'invitations' => []
                ]
            ],
            'Organization with multiple members' => [
                'organization' => new Organization(
                    id: new OrganizationId(value: new Uuid(value: 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23')),
                    name: 'Tech Corp',
                    members: Members::createFrom(elements: [
                        new Member(
                            id: new MemberId(value: new Uuid(value: '88f15d3f-c9b9-4855-9778-5ba7926b6736')),
                            role: 'owner',
                            userId: new UserId(value: new Uuid(value: '4a12fa11-33d1-4ac1-bc15-90af7dbee0c8')),
                            isOwner: true,
                            organizationId: new OrganizationId(
                                value: new Uuid(value: 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23')
                            )
                        ),
                        new Member(
                            id: new MemberId(value: new Uuid(value: 'c23b4c0a-f6d1-4b02-af2a-28b120a0ceb6')),
                            role: 'admin',
                            userId: new UserId(value: new Uuid(value: 'b2c98bc8-c3f2-451b-a476-c4ec6ae23036')),
                            isOwner: false,
                            organizationId: new OrganizationId(
                                value: new Uuid(value: 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23')
                            )
                        )
                    ]),
                    invitations: []
                ),
                'expected'     => [
                    'id'          => 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23',
                    'name'        => 'Tech Corp',
                    'members'     => [
                        [
                            'id'             => '88f15d3f-c9b9-4855-9778-5ba7926b6736',
                            'role'           => 'owner',
                            'userId'         => '4a12fa11-33d1-4ac1-bc15-90af7dbee0c8',
                            'isOwner'        => true,
                            'organizationId' => 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23'
                        ],
                        [
                            'id'             => 'c23b4c0a-f6d1-4b02-af2a-28b120a0ceb6',
                            'role'           => 'admin',
                            'userId'         => 'b2c98bc8-c3f2-451b-a476-c4ec6ae23036',
                            'isOwner'        => false,
                            'organizationId' => 'dc0dbdfd-9f8d-43c9-a000-19bcc989d20a23'
                        ]
                    ],
                    'invitations' => []
                ]
            ],
        ];
    }

    public static function dataProviderForFromIterable(): iterable
    {
        return [
            'Create organization from array with nested data' => [
                'data'     => [
                    'id'          => ['value' => ['value' => '6daca0fb-f718-414d-bdb8-5d1b2d65628b']],
                    'name'        => 'Calenvo',
                    'members'     => [
                        [
                            'id'             => ['value' => ['value' => '08a6ce33-95e7-43db-b566-9620216cdd5a']],
                            'role'           => 'admin',
                            'userId'         => ['value' => ['value' => '2e9f9b9b-febb-4c01-a7f7-f802c2e712d2']],
                            'isOwner'        => true,
                            'organizationId' => ['value' => ['value' => '6daca0fb-f718-414d-bdb8-5d1b2d65628b']]
                        ]
                    ],
                    'invitations' => []
                ],
                'expected' => new Organization(
                    id: new OrganizationId(value: new Uuid(value: '6daca0fb-f718-414d-bdb8-5d1b2d65628b')),
                    name: 'Calenvo',
                    members: Members::createFrom(elements: [
                        new Member(
                            id: new MemberId(value: new Uuid(value: '08a6ce33-95e7-43db-b566-9620216cdd5a')),
                            role: 'admin',
                            userId: new UserId(value: new Uuid(value: '2e9f9b9b-febb-4c01-a7f7-f802c2e712d2')),
                            isOwner: true,
                            organizationId: new OrganizationId(
                                value: new Uuid(value: '6daca0fb-f718-414d-bdb8-5d1b2d65628b')
                            )
                        )
                    ]),
                    invitations: []
                )
            ]
        ];
    }
}
