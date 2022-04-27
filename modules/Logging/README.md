# Elephox Logging Module

This module is used by [Elephox] to log information to one or more destinations (sinks).
It also supports formatting with [ANSI escape codes].

## Example

```php
<?php

use Elephox\Logging\MultiSinkLogger;
use Elephox\Logging\ConsoleSink;
use Elephox\Logging\Contract\Sink;
use Elephox\Logging\LogLevel;

$logger = new MultiSinkLogger();
$logger->addSink(new ConsoleSink());

$logger->info('Hello world!'); // Prints to console: [26.04.22 11:04:10.713] [INF] Hello world!

// You can also log meta data:
$logger->info('Hello world!', ['foo' => 'bar']); // Prints to console: [26.04.22 11:04:10.713] [INF] Hello world! {'foo': 'bar'}

// You can implement your own sink:
class MySink implements Sink
{
    public function write(string $message, LogLevel $level, array $metaData): void
    {
        $this->myThirdPartyLoggingService->log($message, $level->value, $metaData);
    }
}

$logger->addSink(new MySink());
$logger->alert("This is an alert!");
// Prints to console: [26.04.22 11:04:10.713] [ALT] This is an alert!
// ...and logs to your third party logging service
```

[Elephox]: https://github.com/elephox-dev/framework
[ANSI escape codes]: https://en.wikipedia.org/wiki/ANSI_escape_code
