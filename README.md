# Mapper

[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

* [Overview](#overview)
* [Installation](#installation)
* [How to use](#how-to-use)
* [License](#license)
* [Contributing](#contributing)

<div id='overview'></div> 

## Overview

Allows mapping data between different formats, such as JSON, arrays, and DTOs, providing flexibility in transforming and
serializing information.

<div id='installation'></div>

## Installation

```bash
composer require tiny-blocks/mapper
```

<div id='how-to-use'></div>

## How to use

### Object

The library exposes available behaviors through the `ObjectMapper` interface, and the implementation of these behaviors
through the `ObjectMappability` trait.

#### Create an object from an iterable

You can map data from an iterable (such as an array) into an object. Here's how to map a `Shipping` object from an
iterable:

```php
<?php

declare(strict_types=1);

namespace Example;

use TinyBlocks\Mapper\ObjectMappability;
use TinyBlocks\Mapper\ObjectMapper;

final readonly class Shipping implements ObjectMapper
{
    use ObjectMappability;

    public function __construct(public int $id, public ShippingAddresses $addresses)
    {
    }
}
```

Next, define a `ShippingAddresses` class that is iterable:

```php
<?php

declare(strict_types=1);

namespace Example;

use ArrayIterator;use IteratorAggregate;use TinyBlocks\Mapper\ObjectMappability;use TinyBlocks\Mapper\ObjectMapper;use Traversable;

final class ShippingAddresses implements ObjectMapper, IteratorAggregate
{
    use ObjectMappability;

    /**
     * @var \TinyBlocks\Mapper\Models\ShippingAddress[] $elements
     */
    private iterable $elements;

    public function __construct(iterable $elements = [])
    {
        $this->elements = is_array($elements) ? $elements : iterator_to_array($elements);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->elements);
    }
}

```

Now you can map data into a `Shipping` object using `fromIterable`:

```php
<?php

use Example\Shipping;

$shipping = Shipping::fromIterable(iterable: [
    'id'        => PHP_INT_MAX,
    'addresses' => [
        [
            'city'    => 'New York',
            'state'   => 'NY',
            'street'  => '5th Avenue',
            'number'  => 717,
            'country' => 'US'
        ]
    ]
]);
```

#### Map object to array

Once the object is created, you can easily convert it into an array representation.

```php
$shipping->toArray();
```

This will output the following array:

```php
[
    'id'        => 9223372036854775807,
    'addresses' => [
        [
            'city'    => 'New York',
            'state'   => 'NY',
            'street'  => '5th Avenue',
            'number'  => 717,
            'country' => 'US'
        ]
    ]
]
```

#### Map object to JSON

Similarly, you can convert the object into a JSON representation.

```php
$shipping->toJson();
```

This will produce the following JSON:

```json
{
    "id": 9223372036854775807,
    "addresses": [
        {
            "city": "New York",
            "state": "NY",
            "street": "5th Avenue",
            "number": 717,
            "country": "US"
        }
    ]
}
```

<div id='license'></div>

## License

Mapper is licensed under [MIT](LICENSE).

<div id='contributing'></div>

## Contributing

Please follow the [contributing guidelines](https://github.com/tiny-blocks/tiny-blocks/blob/main/CONTRIBUTING.md) to
contribute to the project.
