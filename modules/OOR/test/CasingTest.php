<?php
declare(strict_types=1);

namespace Elephox\OOR;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\OOR\Casing
 *
 * @internal
 */
class CasingTest extends TestCase
{
	public function casingDataProvider(): iterable
	{
		yield ['foo', 'foo', 'FOO', 'Foo', 'foo', 'foo', 'foo', 'Foo', 'FOO', 'Foo'];
		yield ['FOO', 'foo', 'FOO', 'Foo', 'foo', 'foo', 'foo', 'Foo', 'FOO', 'Foo'];
		yield ['FooBar', 'foobar', 'FOOBAR', 'Foobar', 'fooBar', 'foo_bar', 'foobar', 'Foobar', 'FOOBAR', 'FooBar'];
		yield ['Foo Bar', 'foo bar', 'FOO BAR', 'Foo Bar', 'fooBar', 'foo_bar', 'foo-bar', 'Foo-Bar', 'FOO-BAR', 'FooBar'];
		yield ['FoO BaR', 'foo bar', 'FOO BAR', 'Foo Bar', 'foOBaR', 'fo_o_ba_r', 'foo-bar', 'Foo-Bar', 'FOO-BAR', 'FoOBaR'];
		yield ['Foo-Bar', 'foo-bar', 'FOO-BAR', 'Foo-Bar', 'fooBar', 'foo_bar', 'foo-bar', 'Foo-Bar', 'FOO-BAR', 'FooBar'];
		yield ['Foo_Bar', 'foo_bar', 'FOO_BAR', 'Foo_Bar', 'fooBar', 'foo_bar', 'foo-bar', 'Foo-Bar', 'FOO-BAR', 'FooBar'];
		yield ['f%a@d_=', 'f%a@d_=', 'F%A@D_=', 'F%A@D_=', 'f%A@D=', 'f%a@d_=', 'f%a@d-=', 'F%A@D-=', 'F%A@D-=', 'F%A@D='];
		yield ['ConTent-Type', 'content-type', 'CONTENT-TYPE', 'Content-Type', 'conTentType', 'con_tent_type', 'content-type', 'Content-Type', 'CONTENT-TYPE', 'ConTentType'];
	}

	/**
	 * @dataProvider casingDataProvider
	 *
	 * @param string $input
	 * @param string $lower
	 * @param string $upper
	 * @param string $title
	 * @param string $camel
	 * @param string $snake
	 * @param string $kebab
	 * @param string $httpHeader
	 * @param string $cobol
	 * @param string $pascal
	 */
	public function testCasing(string $input, string $lower, string $upper, string $title, string $camel, string $snake, string $kebab, string $httpHeader, string $cobol, string $pascal): void
	{
		static::assertSame($lower, Casing::toLower($input), "Invalid case conversion for: toLower($input)");
		static::assertSame($upper, Casing::toUpper($input), "Invalid case conversion for: toUpper($input)");
		static::assertSame($title, Casing::toTitle($input), "Invalid case conversion for: toTitle($input)");
		static::assertSame($camel, Casing::toCamel($input), "Invalid case conversion for: toCamel($input)");
		static::assertSame($snake, Casing::toSnake($input), "Invalid case conversion for: toSnake($input)");
		static::assertSame($kebab, Casing::toKebab($input), "Invalid case conversion for: toKebab($input)");
		static::assertSame($httpHeader, Casing::toHttpHeader($input), "Invalid case conversion for: toHttpHeader($input)");
		static::assertSame($cobol, Casing::toCobol($input), "Invalid case conversion for: toCobol($input)");
		static::assertSame($pascal, Casing::toPascal($input), "Invalid case conversion for: toPascal($input)");
	}

	public function replaceDelimitersDataProvider(): iterable
	{
		yield ['foo\nbar', 'foo bar', '\n', null];
		yield ['example.com', 'example.com', ' ', null];
		yield ['example com', 'example.com', ' ', '/\\./'];
	}

	/**
	 * @dataProvider replaceDelimitersDataProvider
	 *
	 * @param string $output
	 * @param string $input
	 * @param string $replacement
	 * @param ?string $delimitersPattern
	 */
	public function testReplaceDelimiters(string $output, string $input, string $replacement, ?string $delimitersPattern): void
	{
		if ($delimitersPattern !== null) {
			static::assertSame($output, Casing::replaceDelimiters($input, $replacement, $delimitersPattern));
		} else {
			static::assertSame($output, Casing::replaceDelimiters($input, $replacement));
		}
	}
}
