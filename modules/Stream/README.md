# Elephox Stream Module

This module is used by [Elephox] to ease the work with streams.

## Examples

```php
<?php

use Elephox\Stream\StringStream;
use Elephox\Stream\AppendStream;
use Elephox\Stream\ResourceStream;
use Elephox\Stream\EmptyStream;
use Elephox\Stream\LazyStream;

$stream = new StringStream('Hello World!');

$stream->eof(); // false
$stream->read(5); // 'Hello'
$stream->readByte(); // 32 (space)
$stream->readLine(); // 'World!' (reads to eof or "\r\n")
$stream->eof(); // true

$stream->rewind(); // rewinds to the beginning
$stream->getContents(); // 'Hello World!'

$moreStreams = new AppendStream($stream, new StringStream(' And welcome to Elephox!'));
$moreStreams->rewind();

$moreStreams->readAllLines(eol: " "); // ['Hello', 'World!', 'And', 'welcome', 'to', 'Elephox!']
$moreStreams->rewind();
$moreStreams->read(15); // 'Hello World! An'
$moreStreams->seek(8, SEEK_END); // moves relative from end of streams
$moreStreams->read(8); // 'Elephox!'

// there are more stream types:

// wraps stream resources
$resourceStream = new ResourceStream(fopen('/etc/passwd', 'r'));

// not readable, not writeable, not seekable, always empty
$emptyStream = new EmptyStream();

// only executes the closure if needed
$lazyStream = new LazyStream(function() {
    return new StringStream('Hello World!');
});

// and they are all combinable via the AppendStream:

$megaStream = new AppendStream(
    $moreStreams,
    new AppendStream(
        $lazyStream,
        $resourceStream,
    ),
);

$megaStream->getContents(); // 'Hello World! And welcome to Elephox!Hello World!' + </etc/passwd contents>

$megaStream->close(); // closes all streams and releases the resources
```

[Elephox]: https://github.com/elephox-dev/framework
