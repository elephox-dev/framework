<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Logging\Contract\Sink;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as M;

/**
 * @covers \Elephox\Logging\FormattingConsoleSink
 * @covers \Elephox\Logging\LogLevel
 */
class FormattingConsoleSinkTest extends MockeryTestCase
{
	public function testSimpleWrite(): void
	{
		$sink = M::mock(Sink::class);

		$sink
			->expects('write')
			->with("Hello World", LogLevel::INFO, [])
			->andReturns()
		;

		$formattingConsoleSink = new FormattingConsoleSink($sink);
		$formattingConsoleSink->write("Hello World", LogLevel::INFO, []);
	}

	public function testSimpleFormatWrite(): void
	{
		$sink = M::mock(Sink::class);

		$sink
			->expects('write')
			->with("Hello \033[0;32mWorld\033[0m", LogLevel::INFO, [])
			->andReturns()
		;

		$formattingConsoleSink = new FormattingConsoleSink($sink);
		$formattingConsoleSink->write("Hello <green>World</green>", LogLevel::INFO, []);
	}
}
