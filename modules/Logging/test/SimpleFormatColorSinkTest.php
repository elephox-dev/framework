<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Logging\Contract\Sink;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as M;

/**
 * @covers \Elephox\Logging\SimpleFormatColorSink
 * @covers \Elephox\Logging\LogLevel
 *
 * @internal
 */
class SimpleFormatColorSinkTest extends MockeryTestCase
{
	public function testSimpleWrite(): void
	{
		$sink = M::mock(Sink::class);

		$sink
			->expects('write')
			->with(LogLevel::INFO, 'Hello World', [])
			->andReturns()
		;

		$messageFormatterSink = new SimpleFormatColorSink($sink);
		$messageFormatterSink->write(LogLevel::INFO, 'Hello World', []);
	}

	public function testSimpleFormatWrite(): void
	{
		$sink = M::mock(Sink::class);

		$sink
			->expects('write')
			->with(LogLevel::INFO, "Hello \033[32mWorld\033[39m", [])
			->andReturns()
		;

		$sink
			->expects('write')
			->with(LogLevel::WARNING, "This \033[32mis\033[33m a \033[31mwarning\033[33m", [])
			->andReturns()
		;

		$messageFormatterSink = new SimpleFormatColorSink($sink);
		$messageFormatterSink->write(LogLevel::INFO, 'Hello <green>World</green>', []);
		$messageFormatterSink->write(LogLevel::WARNING, 'This <green>is</green> a <red>warning</red>', []);
	}

	// TODO: write tests for background and options
}
