# Elephox DI Module

This module is used by [Elephox] to provide a dependency injection container.
It also has mechanisms to resolve arguments for functions and callbacks.

## Example

```php
<?php

use Elephox\DI\ServiceCollectionOld;

$container = new ServiceCollectionOld();
$container->addSingleton(stdClass::class, stdClass::class, fn () => new stdClass());

$foo = function (stdClass $object) {
    return $object;
}

$instance = $container->resolver()->call($foo);
// $instance is an instance of stdClass
// multiple calls to $container->resolver()->callback($foo) will return the same instance, since it was added as a singleton
```

[Elephox]: https://github.com/elephox-dev/framework
