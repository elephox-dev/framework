# Elephox Logging Module

This module is used by [Elephox] to log information to one or more destinations (sinks).

## Example

```php
use Elephox\Logging\StandardSink;
use Elephox\Logging\SingleSinkLogger;
use Elephox\Logging\Contract\Sink;
use Elephox\Logging\LogLevel;

// create a logger with only one sink, a sink which logs to STDOUT and STDERR
$logger = new SingleSinkLogger(new StandardSink());

$logger->info('Hello world!'); // Prints to STDOUT: Hello world!
$logger->warning('This is a warning!'); // Prints to STDERR: This is a warning!

// You can also log meta data:
$logger->info('Hello world!', ['foo' => 'bar']);

// You can implement your own sink:
class MySink implements Sink
{
    public function write(string $message, LogLevel $level, array $metaData): void
    {
        $this->myThirdPartyLoggingService->log($message, $level->value, $metaData);
    }
}

$mySinkLogger = new SingleSinkLogger(new MySink());
$mySinkLogger->alert("This is an alert!");
```

You can also wrap sinks in decorator sinks (classes implementing `SinkProxy`):

```php
use Elephox\Logging\SimpleFormatColorSink;
use Elephox\Logging\EnhancedMessageSink;

$standardSink = new StandardSink();
$formattedSink = new SimpleFormatColorSink($standardSink);

$formattedSink->write(LogLevel::INFO, '<blue>Hello</blue> <green>world</green>!', []);
// Prints a blue "Hello" and a green "world" to STDOUT

$enhancedSink = new EnhancedMessageSink($formattedSink);
$enhancedSink->write(LogLevel::INFO, '<blue>Hello</blue> <green>world</green>!', []);
// Prints a blue "Hello" and a green "world" to STDOUT, with a timestamp and a log level
```

[Elephox]: https://github.com/elephox-dev/framework
