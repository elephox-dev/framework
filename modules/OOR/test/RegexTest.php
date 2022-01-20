<?php
declare(strict_types=1);

namespace Elephox\OOR;

use PHPUnit\Framework\TestCase;
use Safe\Exceptions\PcreException;

/**
 * @covers \Elephox\OOR\Regex
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Collection\ArrayMap
 */
class RegexTest extends TestCase
{
	public function testSplit(): void
	{
		$simple = Regex::split('/\s+/', 'hello world');
		self::assertEquals(['hello', 'world'], $simple->toList());

		$multiline = Regex::split('/\n/', "This is\na multiline\ntest");
		self::assertEquals(['This is', 'a multiline', 'test'], $multiline->toList());
	}

	public function testInvalidSplitPattern(): void
	{
		$this->expectException(PcreException::class);
		$this->expectExceptionMessage('PREG_BACKTRACK_LIMIT_ERROR: Backtrack limit reached');

		Regex::split('/(?:\D+|<\d+>)*[!?]/', 'foobar foobar foobar');
	}

	public function testMatch(): void
	{
		$simple = Regex::match('/(?<hello>hello)*/', 'hello world');
		self::assertEquals([
			0 => 'hello',
			1 => 'hello',
			'hello' => 'hello',
		], $simple->toArray());
	}

	public function testMatches(): void
	{
		self::assertTrue(Regex::matches('/(?<hello>hello)*/', 'hello world'));
		self::assertFalse(Regex::matches('/(foo)(bar)(baz)/', 'world'));
	}

	public function testInvalidMatchPattern(): void
	{
		$this->expectException(PcreException::class);
		$this->expectExceptionMessage('PREG_BACKTRACK_LIMIT_ERROR: Backtrack limit reached');

		Regex::match('/(?:\D+|<\d+>)*[!?]/', 'foobar foobar foobar');
	}
}
