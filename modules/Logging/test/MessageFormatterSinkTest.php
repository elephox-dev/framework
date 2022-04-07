<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Logging\Contract\Sink;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as M;

/**
 * @covers \Elephox\Logging\MessageFormatterSink
 * @covers \Elephox\Logging\LogLevel
 *
 * @internal
 */
class MessageFormatterSinkTest extends MockeryTestCase
{
	public function testSimpleWrite(): void
	{
		$sink = M::mock(Sink::class);

		$sink
			->expects('write')
			->with('Hello World', LogLevel::INFO, [])
			->andReturns()
		;

		$messageFormatterSink = new MessageFormatterSink($sink);
		$messageFormatterSink->write('Hello World', LogLevel::INFO, []);
	}

	public function testSimpleFormatWrite(): void
	{
		$sink = M::mock(Sink::class);

		$sink
			->expects('write')
			->with("Hello \033[32mWorld\033[39m", LogLevel::INFO, [])
			->andReturns()
		;

		$sink
			->expects('write')
			->with("This \033[32mis\033[33m a \033[31mwarning\033[33m", LogLevel::WARNING, [])
			->andReturns()
		;

		$messageFormatterSink = new MessageFormatterSink($sink);
		$messageFormatterSink->write('Hello <green>World</green>', LogLevel::INFO, []);
		$messageFormatterSink->write('This <green>is</green> a <red>warning</red>', LogLevel::WARNING, []);
	}

	// TODO: write tests for background and options
}
