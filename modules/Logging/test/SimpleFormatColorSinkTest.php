<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Logging\Contract\Sink;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as M;

/**
 * @covers \Elephox\Logging\SimpleFormatColorSink
 * @covers \Elephox\Logging\LogLevel
 * @covers \Elephox\Logging\SinkCapability
 *
 * @internal
 */
final class SimpleFormatColorSinkTest extends MockeryTestCase
{
	public function testSimpleWrite(): void
	{
		$sink = M::mock(Sink::class);

		$sink
			->expects('hasCapability')
			->with(SinkCapability::AnsiFormatting)
			->andReturns(true)
		;

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
			->allows('hasCapability')
			->with(SinkCapability::AnsiFormatting)
			->andReturns(true)
		;

		$sink
			->expects('write')
			->with(M::capture($firstLogLevel), "Hello \033[32mWorld\033[39m", [])
			->andReturns()
		;

		$sink
			->expects('write')
			->with(M::capture($secondLogLevel), "This \033[32mis\033[39m a \033[31mwarning\033[39m", [])
			->andReturns()
		;

		$messageFormatterSink = new SimpleFormatColorSink($sink);
		$messageFormatterSink->write(LogLevel::INFO, 'Hello <green>World</green>', []);
		self::assertSame(LogLevel::INFO, $firstLogLevel);
		$messageFormatterSink->write(LogLevel::WARNING, 'This <green>is</green> a <red>warning</red>', []);
		self::assertSame(LogLevel::WARNING, $secondLogLevel);
	}

	public function testFormatRemovedWithNoAnsiSupport(): void
	{
		$sink = M::mock(Sink::class);

		$sink
			->allows('hasCapability')
			->with(SinkCapability::AnsiFormatting)
			->andReturns(false)
		;

		$sink
			->expects('write')
			->with(M::capture($firstLogLevel), 'Hello World', [])
			->andReturns()
		;

		$sink
			->expects('write')
			->with(M::capture($secondLogLevel), 'This is a warning', [])
			->andReturns()
		;

		$messageFormatterSink = new SimpleFormatColorSink($sink);
		$messageFormatterSink->write(LogLevel::INFO, 'Hello <green>World</green>', []);
		self::assertSame(LogLevel::INFO, $firstLogLevel);
		$messageFormatterSink->write(LogLevel::WARNING, 'This <green>is</green> a <red>warning</red>', []);
		self::assertSame(LogLevel::WARNING, $secondLogLevel);
	}

	// TODO: write tests for background and options
}
