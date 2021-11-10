<?php

namespace Philly\Text;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Philly\Text\Regex
 * @covers \Philly\Collection\ArrayList
 */
class RegexTest extends TestCase
{
	public function testSplit(): void
	{
		$simple = Regex::split('/\s+/', 'hello world');
		self::assertEquals(['hello', 'world'], $simple->asArray());

		$multiline = Regex::split('/\n/', "This is\na multiline\ntest");
		self::assertEquals(['This is', 'a multiline', 'test'], $multiline->asArray());
	}

	public function testInvalidSplitPattern(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('An error occurred while splitting: Backtrack limit exhausted');

		Regex::split('/(?:\D+|<\d+>)*[!?]/', 'foobar foobar foobar');
	}
}
