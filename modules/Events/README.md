# Elephox Events Module

This module is used by [Elephox] to provide an event bus with broadcasters and listeners.

## Example

```php
<?php

use Elephox\Events\EventBus;

$bus = new EventBus();
$subscription = $bus->subscribe('test', function ($data) {
    echo 'test event: ' . $data;
});

$bus->publish('test', 'test data'); // "test event: test data"

$bus->unsubscribe($subscription);

$bus->publish('test', 'test data'); // no output
```

[Elephox]: https://github.com/elephox-dev/framework
