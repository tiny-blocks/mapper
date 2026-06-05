# Mapper

[![License](https://img.shields.io/badge/license-MIT-green)](https://github.com/tiny-blocks/mapper/blob/main/LICENSE)

* [Overview](#overview)
* [Installation](#installation)
* [How to use](#how-to-use)
    + [The Mapper service](#the-mapper-service)
    + [The Mappable trait](#the-mappable-trait)
    + [Polymorphic types with Subtype](#polymorphic-types-with-subtype)
    + [Flat rows with Layout](#flat-rows-with-layout)
    + [JSON columns with JsonColumn](#json-columns-with-jsoncolumn)
    + [Collections that map themselves](#collections-that-map-themselves)
    + [Scalar codecs with Codec](#scalar-codecs-with-codec)
    + [Self-describing scalars with ScalarCodec](#self-describing-scalars-with-scalarcodec)
    + [Transparent delegation for single-property wrappers](#transparent-delegation-for-single-property-wrappers)
    + [Factory construction with FactoryMethod](#factory-construction-with-factorymethod)
    + [Configuration and naming](#configuration-and-naming)
    + [Exceptions](#exceptions)
* [License](#license)
* [Contributing](#contributing)

## Overview

Maps PHP objects to and from arrays, JSON, and iterables through reflection and pluggable strategies. Handles
backed and pure enums, value objects, nested objects, date-time types, and collections out of the box. Designed
for DTO hydration, serialization at the HTTP boundary, flat-row decoding at the persistence boundary, and data
transfer between bounded contexts.

The library exposes two complementary ways to use it. The primary one is the `Mapper` service, an immutable
builder that keeps the mapped class fully decoupled from the library. No interface to implement, no trait to
use, nothing on the domain side. It fulfills two narrow service contracts, `Serializer` (object to array and
JSON) and `Deserializer` (array, JSON, or iterable to object), so a consumer can depend on the capability it
needs rather than on the concrete service.

The second is the `Mappable` interface plus the `MappableBehavior` trait, an opt-in hook for application DTOs
that prefer to map themselves. `Mappable` combines `Serializable` (render to array and JSON) and
`Deserializable` (build from a source). The mapping logic lives in the mapper, not on the type: objects are
reflected by the engine and collections are built internally, so a `Mappable` type exposes no engine-facing
method. When a configured `Mapper` maps a `Mappable` value, the engine reflects it through the active naming,
so nested children resolve through any registered mappings.

## Installation

```bash
composer require tiny-blocks/mapper
```

## How to use

### The Mapper service

`Mapper::create()` returns an empty mapper with identity naming and lenient unknown keys. The same instance
hydrates from arrays, JSON strings, and iterables, and serializes back to arrays or JSON.

A currency-bearing amount is the value object the mapper hydrates.

```php
<?php

declare(strict_types=1);

final readonly class Amount
{
    public function __construct(public int $amount, public Currency $currency)
    {
    }
}
```

The amount references a backed enum that names the currency.

```php
<?php

declare(strict_types=1);

enum Currency: string
{
    case BRL = 'BRL';
    case USD = 'USD';
}
```

With those in place, the mapper reads and writes them through a single service. `toObject` hydrates from
an array, a JSON string, or any iterable.

```php
<?php

declare(strict_types=1);

use TinyBlocks\Mapper\Mapper;

$mapper = Mapper::create();

$amount = $mapper->toObject(type: Amount::class, source: ['amount' => 50, 'currency' => 'BRL']);
$amount = $mapper->toObject(type: Amount::class, source: '{"amount":50,"currency":"BRL"}');
```

`toObjectOrNull` returns null when the source is null, and behaves like `toObject` otherwise.

```php
$missing = $mapper->toObjectOrNull(type: Amount::class, source: null);
```

The same service serializes back to an array or a JSON string.

```php
$array = $mapper->toArray(source: $amount);
$json = $mapper->toJson(source: $amount);
```

`withNaming`, `withMapping`, and `rejectingUnknownKeys` return a new mapper each time. The original instance
keeps its previous configuration.

```php
<?php

declare(strict_types=1);

use TinyBlocks\Mapper\Mapper;
use TinyBlocks\Mapper\SnakeCase;

$strict = Mapper::create()
    ->withNaming(namingStrategy: SnakeCase::create())
    ->rejectingUnknownKeys();
```

The mapper fulfills two service contracts: `Serializer` (`toArray`, `toJson`, and their `*OrNull` variants)
and `Deserializer` (`toObject`, `toProperties`, `toObjectOrNull`). A consumer can type-hint the narrow
contract it depends on instead of the concrete `Mapper`.

### The Mappable trait

When an application DTO prefers to map itself, implement `Mappable` and use `MappableBehavior`. `Mappable`
combines `Serializable` (the `toArray` and `toJson` output methods) and `Deserializable` (the `buildFrom`
factory). The trait implements all three on top of the same engine that backs the `Mapper` service, so the
type needs no explicit `Mapper` instance at the call site and exposes no engine-facing method.

A two-field address wires itself into the mapper through the trait.

```php
<?php

declare(strict_types=1);

use TinyBlocks\Mapper\Mappable;
use TinyBlocks\Mapper\MappableBehavior;

final readonly class Address implements Mappable
{
    use MappableBehavior;

    public function __construct(public string $city, public string $street)
    {
    }
}
```

The DTO drives its own hydration and serialization.

```php
<?php

declare(strict_types=1);

$address = Address::buildFrom(source: ['street' => 'Av. Paulista', 'city' => 'São Paulo']);

$array = $address->toArray();
$json = $address->toJson();
```

`buildFrom`, `toArray`, and `toJson` map the instance on its own, with identity naming and no registered
mappings. When a configured `Mapper` serializes or hydrates a `Mappable` subject, the engine reflects it
through the active naming, so any mapping registered for a nested type applies. A mapping registered for the
`Mappable` type itself takes precedence over the reflection-based default.

### Polymorphic types with Subtype

`Subtype::by` builds a mapping that selects a concrete class by the value of a discriminator field. It lists the
concrete types only and derives each discriminator value from the type's short name. The naming strategy applied
to the short name produces the value and defaults to snake_case. On read, the field value picks the class. On
write, the class is reverse-looked-up to its derived value, which is written back exactly once.

The abstract parent the discriminator resolves to.

```php
<?php

declare(strict_types=1);

abstract readonly class PaymentMethod
{
}
```

A Pix concrete type carries a payer identifier and a static factory used as the Subtype default.

```php
<?php

declare(strict_types=1);

final readonly class Pix extends PaymentMethod
{
    public function __construct(public string $payerId)
    {
    }

    public static function pending(): Pix
    {
        return new Pix(payerId: 'pending');
    }
}
```

A debit-card concrete type the same discriminator can resolve to.

```php
<?php

declare(strict_types=1);

final readonly class DebitCard extends PaymentMethod
{
    public function __construct(public string $cardNumber)
    {
    }
}
```

The mapping wires both concrete types under a shared discriminator field. The `field` names the discriminator,
`types` are the concrete classes whose short names derive the values, `naming` is the optional convention
(snake_case by default), and `default` is the optional factory used when no case matches.

```php
<?php

declare(strict_types=1);

use TinyBlocks\Mapper\Mapper;
use TinyBlocks\Mapper\Subtype;

$mapper = Mapper::create()->withMapping(
    type: PaymentMethod::class,
    mapping: Subtype::by(
        field: 'type',
        types: [Pix::class, DebitCard::class],
        default: static fn(): Pix => Pix::pending()
    )
);

$method = $mapper->toObject(type: PaymentMethod::class, source: ['type' => 'pix', 'payerId' => 'Alice']);
```

With the snake_case default, `DebitCard` derives `debit_card` and `Pix` derives `pix`, so the listed types alone
define the discriminator values. The optional `default` factory is invoked when no case matches and when the
discriminator field is absent. With no default, an unmatched case raises `UnknownSubtype`. A misconfigured
mapping raises `InvalidSubtypeCase`: when two types derive the same value, or when a registered case is not a
subtype of the mapped type.

### Flat rows with Layout

`Layout::from` builds a mapping for a flat relational row whose columns map onto a nested object graph. Columns
that follow the prefix-derivation convention (`{field}{separator}{subfield}` under the active naming strategy)
are derived and need no entry. Only renamed leaves, columns outside their expected prefix, and JSON-encoded
columns are declared.

A camera with a serial number and a shot counter.

```php
<?php

declare(strict_types=1);

final readonly class Camera
{
    public function __construct(public string $serialNumber, public int $shotCount)
    {
    }
}
```

A studio composed of a main camera and a tag.

```php
<?php

declare(strict_types=1);

final readonly class Studio
{
    public function __construct(public Camera $mainCamera, public string $tag)
    {
    }
}
```

The flat row maps onto the nested studio through prefix derivation.

```php
<?php

declare(strict_types=1);

use TinyBlocks\Mapper\Layout;
use TinyBlocks\Mapper\Mapper;
use TinyBlocks\Mapper\SnakeCase;

$mapper = Mapper::create()
    ->withNaming(namingStrategy: SnakeCase::create())
    ->withMapping(type: Studio::class, mapping: Layout::from(paths: []));

$studio = $mapper->toObject(type: Studio::class, source: [
    'main_camera_serial_number' => 'sn-1',
    'main_camera_shot_count'    => 7,
    'tag'                       => 'studio-a'
]);
```

The empty `paths` array means every column is derived from the property prefix. A non-empty array overrides
specific leaves with a different column name.

### JSON columns with JsonColumn

A `JsonColumn` marks a column as holding a JSON document. On read, the column is decoded and mapped onto the
graph path. On write, the corresponding subgraph is encoded back into the column. Other declared paths combine
freely with JSON-marked ones in the same `Layout::from(paths: [...])` call.

A member identifier wraps a single scalar with a value accessor.

```php
<?php

declare(strict_types=1);

final readonly class MemberId
{
    public function __construct(private string $value)
    {
    }

    public function value(): string
    {
        return $this->value;
    }
}
```

An owner composed of a member identifier and a display name.

```php
<?php

declare(strict_types=1);

final readonly class Owner
{
    public function __construct(public MemberId $memberId, public string $name)
    {
    }
}
```

The mapping decodes the `member` column as JSON onto the `memberId` path.

```php
<?php

declare(strict_types=1);

use TinyBlocks\Mapper\JsonColumn;
use TinyBlocks\Mapper\Layout;
use TinyBlocks\Mapper\Mapper;

$mapper = Mapper::create()->withMapping(
    type: Owner::class,
    mapping: Layout::from(paths: ['memberId' => new JsonColumn(column: 'member')])
);

$owner = $mapper->toObject(
    type: Owner::class,
    source: ['member' => '{"value":"m-1"}', 'name' => 'Alice']
);
```

### Collections that map themselves

PHP carries no runtime element type for a typed collection, so a collection implements `IterableMappable` and
declares its element type with the `#[ElementType]` attribute. On read, the mapper maps each source element to
that type and hands the built elements to `createFrom`. Absence of the attribute means passthrough: elements
are kept as-is. On write, the collection is iterated and each element is serialized through the engine, so a
mapping registered for the element type is honored on every element.

A refund row bound to a single amount.

```php
<?php

declare(strict_types=1);

final readonly class Refund
{
    public function __construct(public string $reference, public Amount $amount)
    {
    }
}
```

A refund collection that maps itself element by element.

```php
<?php

declare(strict_types=1);

use Generator;
use IteratorAggregate;
use TinyBlocks\Mapper\ElementType;
use TinyBlocks\Mapper\IterableMappable;
use TinyBlocks\Mapper\Mapper;

#[ElementType(Refund::class)]
class Refunds implements IteratorAggregate, IterableMappable
{
    private function __construct(public readonly iterable $elements)
    {
    }

    public static function createFrom(iterable $elements): static
    {
        return new static(elements: $elements);
    }

    public function toJson(): string
    {
        return Mapper::create()->toJson(source: $this);
    }

    public function toArray(): array
    {
        return Mapper::create()->toArray(source: $this);
    }

    public function getIterator(): Generator
    {
        foreach ($this->elements as $key => $element) {
            yield $key => $element;
        }
    }
}
```

Mapping a list of refund rows into the collection needs no registration. The mapper builds each element from
the declared `#[ElementType]` and hands the result to `createFrom`.

```php
<?php

declare(strict_types=1);

use TinyBlocks\Mapper\Mapper;

$mapper = Mapper::create();

$refunds = $mapper->toObject(type: Refunds::class, source: [
    ['reference' => 'r-1', 'amount' => ['amount' => 100, 'currency' => 'BRL']],
    ['reference' => 'r-2', 'amount' => ['amount' => 200, 'currency' => 'BRL']]
]);
```

The `tiny-blocks/collection` library ships this behavior built in. Its `Collection` base class already
implements `IterableMappable`, so a typed collection only declares its element with the `#[ElementType]`
attribute.

### Scalar codecs with Codec

`Codec::from` builds a mapping for a value object that has a single canonical scalar form, when the default reflection
would not reproduce it. A typical case is a value object that wraps an inner representation whose direct serialization
drifts from the canonical form, for example a date-only value that would otherwise widen to a full datetime. The
consumer owns both conversions, so the library stays decoupled from the wrapped type.

A calendar date that wraps an inner value but presents a canonical date-only string.

```php
<?php

declare(strict_types=1);

use DateTimeImmutable;

final readonly class CalendarDate
{
    private function __construct(private DateTimeImmutable $value)
    {
    }

    public static function fromIso(string $iso): CalendarDate
    {
        return new CalendarDate(value: DateTimeImmutable::createFromFormat('!Y-m-d', $iso));
    }

    public function toIso(): string
    {
        return $this->value->format('Y-m-d');
    }
}
```

A reservation composed of a single calendar date.

```php
<?php

declare(strict_types=1);

final readonly class Reservation
{
    public function __construct(public CalendarDate $checkIn)
    {
    }
}
```

The codec pins the canonical scalar form on both directions.

```php
<?php

declare(strict_types=1);

use TinyBlocks\Mapper\Codec;
use TinyBlocks\Mapper\Mapper;

$mapper = Mapper::create()->withMapping(
    type: CalendarDate::class,
    mapping: Codec::from(
        decode: static fn(string $iso): CalendarDate => CalendarDate::fromIso(iso: $iso),
        encode: static fn(CalendarDate $date): string => $date->toIso()
    )
);

# The encode closure drives every write, so the nested date stays a canonical YYYY-MM-DD string.
$row = $mapper->toArray(source: new Reservation(checkIn: CalendarDate::fromIso(iso: '2026-05-23')));

# A bare scalar is decoded directly into the value object.
$checkIn = $mapper->toObject(type: CalendarDate::class, source: '2026-05-23');
```

The encode closure is consulted for every write of the type, nested or top-level. The decode closure is consulted
whenever the type is resolved, top-level or as a nested scalar property: a registered mapping takes precedence over
the built-in single-property unwrap. Only unregistered single-value wrappers fall back to that unwrap.

### Self-describing scalars with ScalarCodec

`#[ScalarCodec]` is the self-describing twin of `Codec`: rather than registering closures, a value object names
the methods that convert it to and from a scalar, and the mapper calls them with no configuration. Each attribute
is one decode and encode pair, and the attribute is repeatable. On read, the pair whose decode parameter type
accepts the source scalar is selected, so a type can be built from more than one scalar form. On write, the first
declared pair's encode is used. The attribute adds no public methods: it names methods the type already has. A
mapping registered through `withMapping` for the same type takes precedence over the attribute.

A version that builds from a label string or a release number, and renders back to its label.

```php
<?php

declare(strict_types=1);

use TinyBlocks\Mapper\ScalarCodec;

#[ScalarCodec(decode: 'fromLabel', encode: 'toLabel')]
#[ScalarCodec(decode: 'fromNumber', encode: 'toLabel')]
final readonly class Version
{
    private function __construct(private string $label)
    {
    }

    public static function fromLabel(string $label): Version
    {
        return new Version(label: $label);
    }

    public static function fromNumber(int $number): Version
    {
        return new Version(label: (string) $number);
    }

    public function toLabel(): string
    {
        return $this->label;
    }
}
```

A release composed of a single version.

```php
<?php

declare(strict_types=1);

final readonly class Release
{
    public function __construct(public Version $version)
    {
    }
}
```

No registration is needed: the attribute carries the conversion in both directions.

```php
<?php

declare(strict_types=1);

use TinyBlocks\Mapper\Mapper;

$mapper = Mapper::create();

# The string source selects the decode whose parameter is typed string.
$fromLabel = $mapper->toObject(type: Version::class, source: 'v7');

# The integer source, nested in a release, selects the decode whose parameter is typed int.
$release = $mapper->toObject(type: Release::class, source: ['version' => 7]);

# The first declared pair's encode renders the scalar form.
$label = $mapper->toArray(source: Version::fromLabel(label: 'v7'));
```

A mapping is specified in one of two ways. Registered mappings are attached through `withMapping`: `Codec`,
`FactoryMethod`, `Layout`, and `Subtype` each build a `Mapping` the mapper consults first. Self-describing types
carry the rule themselves: the `Mappable` and `IterableMappable` interfaces, and the `#[ElementType]` and
`#[ScalarCodec]` attributes. A registered mapping always wins over a self-describing one for the same type.

### Transparent delegation for single-property wrappers

A type with a single property needs no mapping of its own to share the scalar form of the value it wraps. When
that single property reduces to a scalar, the mapper unwraps the type on write and rebuilds it on read,
delegating to the inner type's own mapping. The wrapper declares nothing, and the delegation recurses through
nested wrappers until it reaches the scalar.

A property reduces to a scalar when its type is a native scalar, a backed enum, a `DateTimeInterface`, a type
annotated with `#[ScalarCodec]`, or, recursively, another single-property type whose own property reduces to a
scalar. A pure enum reduces to its case name. A `Traversable` never reduces, and neither does an object
with two nor more properties.

A priority label that wraps a backed enum and declares no mapping.

```php
<?php

declare(strict_types=1);

enum Priority: string
{
    case LOW = 'low';
    case HIGH = 'high';
}

final readonly class Label
{
    public function __construct(public Priority $priority)
    {
    }
}
```

A task composed of a name and a single label.

```php
<?php

declare(strict_types=1);

final readonly class Task
{
    public function __construct(public string $name, public Label $label)
    {
    }
}
```

The label collapses to the backing value on write and rebuilds from the same scalar on read.

```php
<?php

declare(strict_types=1);

use TinyBlocks\Mapper\Mapper;

$mapper = Mapper::create();

# The label adopts the backed enum's scalar form, so the column holds a bare value.
$row = $mapper->toArray(source: new Task(name: 'deploy', label: new Label(priority: Priority::HIGH)));

# The same scalar reconstructs the nested label.
$task = $mapper->toObject(type: Task::class, source: ['name' => 'deploy', 'label' => 'high']);
```

The serialized form of the single property follows the inner type.

| Inner type of the single property                | Serialized form                         |
|--------------------------------------------------|-----------------------------------------|
| A native scalar (int, float, string, bool).      | The scalar itself.                      |
| A backed enum.                                   | The backing value.                      |
| A pure enum.                                     | The case name.                          |
| A `DateTimeInterface`.                           | The ISO 8601 string.                    |
| A type annotated with `#[ScalarCodec]`.          | The encoded scalar.                     |
| A single-property type that reduces to a scalar. | The funneled inner scalar.              |
| A `Traversable` collection.                      | A nested array under the property key.  |
| An object with two or more properties.           | A nested object under the property key. |

Delegation is the fallback for a single-property type that neither registers a mapping nor self-describes. To
take over the scalar form, make the wrapper itself carry the rule: register a mapping through `withMapping`, or
annotate the wrapper with `#[ScalarCodec]`. The order of precedence is a registered mapping first, then a
`#[ScalarCodec]` on the wrapper, then delegation, then plain reflection. A wrapper that owns a `Codec` or a
`#[ScalarCodec]` always wins over the delegation to its inner type.

### Factory construction with FactoryMethod

`FactoryMethod::using` builds a mapping that constructs the target through one of its own public static factory methods,
reflecting the factory parameters from the source. It complements `Codec`: where a codec converts a scalar through
closures, a factory mapping is reflection-based and works for any arity. The mapped type imports nothing from the
library, and the factory drives the real construction path (invariants, lookups, parsing) that plain reflection
injection would skip.

A money value object reconstructed through a named factory that normalizes the currency code.

```php
<?php

declare(strict_types=1);

final readonly class Money
{
    private function __construct(public int $cents, public string $currency)
    {
    }

    public static function of(int $cents, string $currency): Money
    {
        return new Money(cents: $cents, currency: strtoupper($currency));
    }
}
```

The mapping is registered like any other, and the factory drives every read.

```php
<?php

declare(strict_types=1);

use TinyBlocks\Mapper\FactoryMethod;
use TinyBlocks\Mapper\Mapper;

$mapper = Mapper::create()->withMapping(
    type: Money::class,
    mapping: FactoryMethod::using(method: 'of')
);

# Each parameter is resolved from the array by its name, then passed to the factory.
$money = $mapper->toObject(type: Money::class, source: ['cents' => 500, 'currency' => 'brl']);

# Reflection over the declared properties writes the array back, symmetric with the read input.
$row = $mapper->toArray(source: $money);
```

The factory parameter names must match the target's property names. Each parameter is resolved by its name under the
active `NamingStrategy`, with scalar coercion and recursive mapping, honoring any registered mapping for nested types.
A single-parameter factory is fed the scalar source directly. A multi-parameter factory is fed an array, so a
top-level multi-parameter source must be an array, not a JSON string.

Writing is reflection over the instance's declared properties, not the inverse of the factory. A single-property
object writes back to a scalar and a compound one to an array, so the round-trip is lossless only when the persisted
form is the canonical form the factory consumes.

`Layout::from(paths: [...], factory: 'of')` composes the two: the flat row is reshaped onto the nested graph and the
final object is built through the factory instead of reflection injection. The nested values inside resolve through
the registry, just as a top-level factory mapping does.

### Configuration and naming

`Configuration` carries per-call output options. The default preserves keys and omits no fields. `omitting`
excludes properties from the output. `discardingKeys` reindexes iterable content with numeric keys, switching
the underlying `KeyPreservation` from `PRESERVE` to `DISCARD`.

A profile with a name, an optional title, a creation timestamp, and a severity.

```php
<?php

declare(strict_types=1);

use DateTimeImmutable;

final readonly class Profile
{
    public function __construct(
        public string $name,
        public ?string $title,
        public DateTimeImmutable $createdAt,
        public Severity $severity
    ) {
    }
}
```

A pure enum that classifies the profile severity.

```php
<?php

declare(strict_types=1);

enum Severity
{
    case LOW;
    case HIGH;
}
```

The profile is hydrated through the mapper from a source array, so `omitting` operates on a visible typed
property. The `$refunds` collection from the previous section is reindex with numeric keys.

```php
<?php

declare(strict_types=1);

use TinyBlocks\Mapper\Configuration;
use TinyBlocks\Mapper\Mapper;

$mapper = Mapper::create();

$profile = $mapper->toObject(type: Profile::class, source: [
    'name'      => 'Alice',
    'title'     => 'Owner',
    'createdAt' => '2026-01-01T00:00:00+00:00',
    'severity'  => 'HIGH'
]);

$array = $mapper->toArray(
    source: $profile,
    configuration: Configuration::default()->omitting('title')
);

$reindex = $mapper->toArray(
    source: $refunds,
    configuration: Configuration::default()->discardingKeys()
);
```

`NamingStrategy` is the interface controlling how source keys translate to property names. `Identity` (the
default) expects keys that already match property names. `SnakeCase` translates between snake_case source keys
and camelCase properties, and it also drives the prefix-derivation column names used by `Layout`. A custom
convention implements `NamingStrategy` directly, defining `toSourceKey` (a property name to its source key) and
`derivedColumn` (the ordered property-path segments to a flat column name).

```php
Mapper::create()->withNaming(namingStrategy: SnakeCase::create());
```

### Exceptions

The library raises four public exceptions, all under `TinyBlocks\Mapper\Exceptions`.

| Exception            | Raised when                                                                              |
|----------------------|------------------------------------------------------------------------------------------|
| `UnmappableSource`   | The source value cannot be mapped to the requested type (malformed JSON, type mismatch). |
| `UnknownSubtype`     | A `Subtype` value matches no case and no default factory is configured.                  |
| `UnexpectedKey`      | A source key matches no property and the mapper was built with `rejectingUnknownKeys()`. |
| `InvalidSubtypeCase` | A `Subtype` maps a case outside the registered type, or derives one value for two types. |

## License

Mapper is licensed under [MIT](LICENSE).

## Contributing

Please follow the [contributing guidelines](https://github.com/tiny-blocks/tiny-blocks/blob/main/CONTRIBUTING.md) to
contribute to the project.
