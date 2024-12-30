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

The examples demonstrate how to create objects from iterables, map objects to arrays, and convert objects to JSON.

### Create an object from an iterable

First, define your classes using the `ObjectMapper` interface and `ObjectMappability` trait:

```php
<?php

declare(strict_types=1);

namespace Example;

use TinyBlocks\Mapper\ObjectMappability;
use TinyBlocks\Mapper\ObjectMapper;

final readonly class ShippingAddress implements ObjectMapper
{
    use ObjectMappability;

    public function __construct(
        private string $city,
        private ShippingState $state,
        private string $street,
        private int $number,
        private ShippingCountry $country
    ) {
    }
}
```

Next, define a collection class implementing `IterableMapper`:

```php
<?php

declare(strict_types=1);

namespace Example;

use TinyBlocks\Collection\Collection;
use TinyBlocks\Mapper\IterableMappability;
use TinyBlocks\Mapper\IterableMapper;

final class ShippingAddresses extends Collection implements IterableMapper
{
    use IterableMappability;

    public function getType(): string
    {
        return ShippingAddress::class;
    }
}
```

Finally, create a class that uses the collection:

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

### Map object to array

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

### Map object to JSON

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
