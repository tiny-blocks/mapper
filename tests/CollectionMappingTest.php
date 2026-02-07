<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Article;
use Test\TinyBlocks\Mapper\Models\Merchant;
use Test\TinyBlocks\Mapper\Models\Store;
use Test\TinyBlocks\Mapper\Models\Stores;
use Test\TinyBlocks\Mapper\Models\Team;

final class CollectionMappingTest extends TestCase
{
    public function testNestedCollectionToArray(): void
    {
        /** @Given a Merchant with a nested Stores collection */
        $merchant = new Merchant(
            id: 'merchant-123',
            stores: Stores::createFrom(elements: [
                new Store(id: 'store-1', name: 'Store A', active: true),
                new Store(id: 'store-2', name: 'Store B', active: false)
            ])
        );

        /** @When converting to array */
        $actual = $merchant->toArray();

        /** @Then the nested collection should be converted */
        self::assertSame('merchant-123', $actual['id']);
        self::assertIsArray($actual['stores']);
        self::assertCount(2, $actual['stores']);
        self::assertSame([
            'id'     => 'store-1',
            'name'   => 'Store A',
            'active' => true
        ], $actual['stores'][0]);
        self::assertSame([
            'id'     => 'store-2',
            'name'   => 'Store B',
            'active' => false
        ], $actual['stores'][1]);
    }

    public function testEmptyNestedCollection(): void
    {
        /** @Given a Merchant with an empty Stores collection */
        $merchant = new Merchant(
            id: 'merchant-empty',
            stores: Stores::createFrom(elements: [])
        );

        /** @When converting to array */
        $actual = $merchant->toArray();

        /** @Then stores should be an empty array */
        self::assertSame([], $actual['stores']);
    }

    public function testCollectionIsIterable(): void
    {
        /** @Given a Stores collection with elements */
        $stores = Stores::createFrom(elements: [
            new Store(id: 's1', name: 'A', active: true),
            new Store(id: 's2', name: 'B', active: true)
        ]);

        /** @When iterating over the collection */
        $count = 0;
        foreach ($stores as $store) {
            $count++;
            self::assertInstanceOf(Store::class, $store);
        }

        /** @Then the count should match the number of elements */
        self::assertSame(2, $count);
        self::assertSame(2, $stores->count());
    }

    public function testNestedCollectionFromIterable(): void
    {
        /** @Given data for a Merchant with Store objects */
        $data = [
            'id'     => 'merchant-456',
            'stores' => [
                new Store(id: 'store-a', name: 'Alpha', active: true),
                new Store(id: 'store-b', name: 'Beta', active: false)
            ]
        ];

        /** @When creating from iterable */
        $merchant = Merchant::fromIterable(iterable: $data);

        /** @Then the Merchant should contain the Stores collection */
        $actual = $merchant->toArray();

        self::assertSame('merchant-456', $actual['id']);
        self::assertCount(2, $actual['stores']);
        self::assertSame('store-a', $actual['stores'][0]['id']);
        self::assertSame('store-b', $actual['stores'][1]['id']);
    }

    public function testCollectionWithDefaultValuesOnElements(): void
    {
        /** @Given a Team with employees where some have missing optional properties */
        $data = [
            'id'        => 'team-1',
            'employees' => [
                ['name' => 'Alice', 'department' => 'engineering', 'active' => true],
                ['name' => 'Bob']
            ]
        ];

        /** @When creating Team from iterable */
        $team = Team::fromIterable(iterable: $data);

        /** @Then defaults should be applied to missing properties */
        $actual = $team->toArray();

        self::assertSame('team-1', $actual['id']);
        self::assertCount(2, $actual['employees']);

        self::assertSame('Alice', $actual['employees'][0]['name']);
        self::assertSame('engineering', $actual['employees'][0]['department']);
        self::assertTrue($actual['employees'][0]['active']);

        self::assertSame('Bob', $actual['employees'][1]['name']);
        self::assertSame('general', $actual['employees'][1]['department']);
        self::assertTrue($actual['employees'][1]['active']);
    }

    public function testCollectionWithNoConstructorElements(): void
    {
        /** @Given an Article with Tags whose element type has no constructor */
        $data = [
            'title' => 'My Article',
            'tags'  => [
                ['name' => 'php', 'color' => 'blue'],
                ['name' => 'testing', 'color' => 'green']
            ]
        ];

        /** @When creating Article from iterable */
        $article = Article::fromIterable(iterable: $data);

        /** @Then Tag elements should have default values since Tag has no constructor */
        $actual = $article->toArray();

        self::assertSame('My Article', $actual['title']);
        self::assertCount(2, $actual['tags']);
        self::assertSame('', $actual['tags'][0]['name']);
        self::assertSame('gray', $actual['tags'][0]['color']);
        self::assertSame('', $actual['tags'][1]['name']);
        self::assertSame('gray', $actual['tags'][1]['color']);
    }
}
