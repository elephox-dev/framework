<?php
declare(strict_types=1);

namespace Elephox\OOR;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\OOR\Regex
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Collection\ArrayMap
 *
 * @internal
 */
class RegexTest extends TestCase
{
	public function testSplit(): void
	{
		$simple = Regex::split('/\s+/', 'hello world');
		static::assertEquals(['hello', 'world'], $simple->toList());

		$multiline = Regex::split('/\n/', "This is\na multiline\ntest");
		static::assertEquals(['This is', 'a multiline', 'test'], $multiline->toList());
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
		static::assertEquals([
			0 => 'hello',
			1 => 'hello',
			'hello' => 'hello',
		], $simple->toArray());
	}

	public function testMatches(): void
	{
		static::assertTrue(Regex::matches('/(?<hello>hello)*/', 'hello world'));
		static::assertFalse(Regex::matches('/(foo)(bar)(baz)/', 'world'));
	}

	public function testInvalidMatchPattern(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('An error occurred while matching: Backtrack limit exhausted');

		Regex::match('/(?:\D+|<\d+>)*[!?]/', 'foobar foobar foobar');
	}

	public function testSpecificity(): void
	{
		static::assertGreaterThan(0, Regex::specificity('/(foo)(bar)(baz)/', 'world'));
		static::assertEquals(1, Regex::specificity('/hello world/', 'hello world'));
		static::assertLessThanOrEqual(1, Regex::specificity('/[a-z]+@[a-z]+\.[a-z]+/', 'alice@foo.com'));
	}

	public function relativeSpecificityDataProvider(): iterable
	{
		yield ['/alice@[a-z]+\.[a-z]+/', '/[a-z]+@[a-z]+\.[a-z]+/', 'alice@foo.com', false];
		yield ['/alice@[a-z]+\.[a-z]+/', '/.*/', 'alice@foo.com', false];
		yield ['/[a-z]+@[a-z]+\.[a-z]+/', '/.*/', 'alice@foo.com', false];
		yield ['/[a-z]+@[a-z]+\.[a-z]+/', '/.*@.*\..*/', 'alice@foo.com', true];
		yield ['/^foo$/', '/^.*$/', 'foo', false];
	}

	/**
	 * @dataProvider relativeSpecificityDataProvider
	 *
	 * @param string $more
	 * @param string $less
	 * @param string $subject
	 * @param bool $equalAllowed
	 */
	public function testRelativeSpecificity(string $more, string $less, string $subject, bool $equalAllowed): void
	{
		$shouldBeMore = Regex::specificity($more, $subject);
		$shouldBeLess = Regex::specificity($less, $subject);

		if ($equalAllowed) {
			static::assertLessThanOrEqual($shouldBeMore, $shouldBeLess);
		} else {
			static::assertLessThan($shouldBeMore, $shouldBeLess);
		}
	}
}
