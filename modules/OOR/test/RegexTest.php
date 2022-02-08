<?php
declare(strict_types=1);

namespace Elephox\OOR;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

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
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('An error occurred while splitting: Backtrack limit exhausted');

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
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('An error occurred while matching: Backtrack limit exhausted');

		Regex::match('/(?:\D+|<\d+>)*[!?]/', 'foobar foobar foobar');
	}

	public function testSpecificity(): void
	{
		self::assertGreaterThan(0, Regex::specificity('/(foo)(bar)(baz)/', 'world'));
		self::assertEquals(0, Regex::specificity('/hello world/', 'hello world'));

		self::assertLessThanOrEqual(1, Regex::specificity('/[a-z]+@[a-z]+\.[a-z]+/', 'alice@foo.com'));
		self::assertLessThan(Regex::specificity('/alice@[a-z]+\.[a-z]+/', 'alice@foo.com'), Regex::specificity('/[a-z]+@[a-z]+\.[a-z]+/', 'alice@foo.com'));
		self::assertLessThan(Regex::specificity('/alice@[a-z]+\.[a-z]+/', 'alice@foo.com'), Regex::specificity('/.*/', 'alice@foo.com'));
		self::assertLessThan(Regex::specificity('/[a-z]+@[a-z]+\.[a-z]+/', 'alice@foo.com'), Regex::specificity('/.*/', 'alice@foo.com'));
	}
}
