<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Mapper\Unit;

use PHPUnit\Framework\TestCase;
use Test\TinyBlocks\Mapper\Models\Camera;
use Test\TinyBlocks\Mapper\Models\Charge;
use Test\TinyBlocks\Mapper\Models\Holder;
use Test\TinyBlocks\Mapper\Models\MemberId;
use Test\TinyBlocks\Mapper\Models\Owner;
use Test\TinyBlocks\Mapper\Models\PaymentMethod;
use Test\TinyBlocks\Mapper\Models\Pix;
use Test\TinyBlocks\Mapper\Models\Studio;
use TinyBlocks\Mapper\Codec;
use TinyBlocks\Mapper\JsonColumn;
use TinyBlocks\Mapper\Mapper;
use TinyBlocks\Mapper\SnakeCase;
use TinyBlocks\Mapper\Subtype;

final class ToPropertiesTest extends TestCase
{
    public function testToPropertiesWhenScalarColumnIsAbsentThenPropertyIsOmitted(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a Camera flat row missing the shot count column is resolved into properties */
        $properties = $mapper->toProperties(type: Camera::class, paths: [], source: ['serialNumber' => 'sn-1']);

        /** @Then the absent property is omitted from the map */
        self::assertEquals(['serialNumber' => 'sn-1'], $properties);
    }

    public function testToPropertiesWhenSnakeCaseNamingThenDerivedColumnsAreResolved(): void
    {
        /** @Given a mapper with snake_case naming */
        $mapper = Mapper::create()->withNaming(namingStrategy: SnakeCase::create());

        /** @When a Studio flat row with snake_case derived camera columns is resolved into properties */
        $properties = $mapper->toProperties(type: Studio::class, paths: [], source: [
            'tag'                       => 'studio-a',
            'main_camera_shot_count'    => 7,
            'main_camera_serial_number' => 'sn-1'
        ]);

        /** @Then the snake_case columns are resolved and keyed by property name */
        self::assertEquals(
            ['tag' => 'studio-a', 'mainCamera' => new Camera(shotCount: 7, serialNumber: 'sn-1')],
            $properties
        );
    }

    public function testToPropertiesWhenNullableColumnHoldsNullThenPropertyIsKeyedWithNull(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a Holder flat row holding a null member id is resolved into properties */
        $properties = $mapper->toProperties(type: Holder::class, paths: [], source: ['memberId' => null]);

        /** @Then the nullable property is present with a null value */
        self::assertEquals(['memberId' => null], $properties);
    }

    public function testToPropertiesWhenEveryNestedColumnIsAbsentThenNestedPropertyIsOmitted(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a Studio flat row carrying no camera columns is resolved into properties */
        $properties = $mapper->toProperties(type: Studio::class, paths: [], source: ['tag' => 'studio-a']);

        /** @Then the nested property whose columns are all absent is omitted */
        self::assertEquals(['tag' => 'studio-a'], $properties);
    }

    public function testToPropertiesWhenNestedColumnsAreDerivedByPrefixThenNestedObjectIsBuilt(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a Studio flat row with prefix-derived camera columns is resolved into properties */
        $properties = $mapper->toProperties(type: Studio::class, paths: [], source: [
            'tag'                    => 'studio-a',
            'mainCameraShotCount'    => 7,
            'mainCameraSerialNumber' => 'sn-1'
        ]);

        /** @Then the nested camera is built and keyed by property name */
        self::assertEquals(
            ['tag' => 'studio-a', 'mainCamera' => new Camera(shotCount: 7, serialNumber: 'sn-1')],
            $properties
        );
    }

    public function testToPropertiesWhenScalarColumnsArePresentThenResolvedAndKeyedByPropertyName(): void
    {
        /** @Given a mapper with default settings */
        $mapper = Mapper::create();

        /** @When a Camera flat row carrying both scalar columns is resolved into properties */
        $properties = $mapper->toProperties(
            type: Camera::class,
            paths: [],
            source: ['serialNumber' => 'sn-1', 'shotCount' => 50]
        );

        /** @Then the scalars are resolved and keyed by property name */
        self::assertEquals(['shotCount' => 50, 'serialNumber' => 'sn-1'], $properties);
    }

    public function testToPropertiesWhenPropertyTypeHasCodecMappingThenValueIsResolvedThroughCodec(): void
    {
        /** @Given a Codec converting a scalar member id into a MemberId */
        $codec = Codec::from(
            decode: static fn(string $value): MemberId => new MemberId(value: strtoupper($value)),
            encode: static fn(MemberId $memberId): string => $memberId->value()
        );

        /** @And a mapper registering the Codec for MemberId */
        $mapper = Mapper::create()->withMapping(type: MemberId::class, mapping: $codec);

        /** @When an Owner flat row carrying a scalar member id is resolved into properties */
        $properties = $mapper->toProperties(
            type: Owner::class,
            paths: [],
            source: ['memberId' => 'm-1', 'name' => 'Alice']
        );

        /** @Then the member id is resolved through the registered Codec */
        self::assertEquals(['name' => 'Alice', 'memberId' => new MemberId(value: 'M-1')], $properties);
    }

    public function testToPropertiesWhenJsonColumnTypeHasSubtypeMappingThenConcreteIsDecodedAndBuilt(): void
    {
        /** @Given a Subtype mapping selecting the concrete PaymentMethod by a discriminator field */
        $subtype = Subtype::by(field: 'type', types: [Pix::class]);

        /** @And a mapper registering the Subtype for PaymentMethod */
        $mapper = Mapper::create()->withMapping(type: PaymentMethod::class, mapping: $subtype);

        /** @When a Charge flat row whose JSON column holds a Pix payload is resolved into properties */
        $properties = $mapper->toProperties(
            type: Charge::class,
            paths: ['paymentMethod' => new JsonColumn(column: 'payment_method')],
            source: ['payment_method' => '{"type":"pix","payerId":"alice"}']
        );

        /** @Then the JSON column is decoded and built through the registered Subtype mapping */
        self::assertEquals(['paymentMethod' => new Pix(payerId: 'alice')], $properties);
    }
}
