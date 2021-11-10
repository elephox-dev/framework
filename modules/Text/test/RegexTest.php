<?php

namespace Philly\Text;

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
}
