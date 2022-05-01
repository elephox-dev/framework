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
	public function testToLower(): void
	{
		static::assertEquals('foo', Casing::toLower('Foo'));
		static::assertEquals('foo', Casing::toLower('foo'));
		static::assertEquals('foobar', Casing::toLower('FooBar'));
		static::assertEquals('foo bar', Casing::toLower('foO Bar'));
		static::assertEquals('foo-bar', Casing::toLower('foO-Bar'));
		static::assertEquals('foo_bar', Casing::toLower('FOO_BAR'));
	}

	public function testToUpper(): void
	{
		static::assertEquals('FOO', Casing::toUpper('Foo'));
		static::assertEquals('FOO', Casing::toUpper('foo'));
		static::assertEquals('FOOBAR', Casing::toUpper('FooBar'));
		static::assertEquals('FOO BAR', Casing::toUpper('foO Bar'));
		static::assertEquals('FOO-BAR', Casing::toUpper('foO-Bar'));
		static::assertEquals('FOO_BAR', Casing::toUpper('foo_bar'));
	}

	public function testToTitle(): void
	{
		static::assertEquals('Foo', Casing::toTitle('foo'));
		static::assertEquals('Foo', Casing::toTitle('Foo'));
		static::assertEquals('Foobar', Casing::toTitle('FooBar'));
		static::assertEquals('Foo Bar', Casing::toTitle('foo bar'));
		static::assertEquals('Foo Bar', Casing::toTitle('Foo Bar'));
		static::assertEquals('Foo Bar', Casing::toTitle('FOO BAR'));
		static::assertEquals('Foo-Bar', Casing::toTitle('foo-bar'));
		static::assertEquals('Foo-Bar', Casing::toTitle('Foo-Bar'));
		static::assertEquals('Foo-Bar', Casing::toTitle('FOO-BAR'));
		static::assertEquals('Foo_Bar', Casing::toTitle('foo_bar'));
		static::assertEquals('Foo_Bar', Casing::toTitle('Foo_Bar'));
		static::assertEquals('Foo_Bar', Casing::toTitle('FOO_BAR'));
	}

	public function testReplaceDelimiters(): void
	{
		static::assertEquals('foo\nbar', Casing::replaceDelimiters('foo bar', '\n'));
		static::assertEquals('example.com', Casing::replaceDelimiters('example.com', ' '));
	}

	public function testToCamel(): void
	{
		static::assertEquals('foo', Casing::toCamel('foo'));
		static::assertEquals('foo', Casing::toCamel('Foo'));
		static::assertEquals('foobar', Casing::toCamel('FooBar'));
		static::assertEquals('fooBar', Casing::toCamel('foo bar'));
		static::assertEquals('fooBar', Casing::toCamel('Foo Bar'));
		static::assertEquals('fooBar', Casing::toCamel('FOO BAR'));
		static::assertEquals('fooBar', Casing::toCamel('foo-bar'));
		static::assertEquals('fooBar', Casing::toCamel('Foo-Bar'));
		static::assertEquals('fooBar', Casing::toCamel('FOO-BAR'));
		static::assertEquals('fooBar', Casing::toCamel('foo_bar'));
		static::assertEquals('fooBar', Casing::toCamel('Foo_Bar'));
		static::assertEquals('fooBar', Casing::toCamel('FOO_BAR'));
	}

	public function testToSnake(): void
	{
		static::assertEquals('foo', Casing::toSnake('foo'));
		static::assertEquals('foo', Casing::toSnake('Foo'));
		static::assertEquals('foobar', Casing::toSnake('FooBar'));
		static::assertEquals('foo_bar', Casing::toSnake('foo bar'));
		static::assertEquals('foo_bar', Casing::toSnake('Foo Bar'));
		static::assertEquals('foo_bar', Casing::toSnake('FOO BAR'));
		static::assertEquals('foo_bar', Casing::toSnake('foo-bar'));
		static::assertEquals('foo_bar', Casing::toSnake('Foo-Bar'));
		static::assertEquals('foo_bar', Casing::toSnake('FOO-BAR'));
		static::assertEquals('foo_bar', Casing::toSnake('foo_bar'));
		static::assertEquals('foo_bar', Casing::toSnake('Foo_Bar'));
		static::assertEquals('foo_bar', Casing::toSnake('FOO_BAR'));
	}

	public function testToKebab(): void
	{
		static::assertEquals('foo', Casing::toKebab('foo'));
		static::assertEquals('foo', Casing::toKebab('Foo'));
		static::assertEquals('foobar', Casing::toKebab('FooBar'));
		static::assertEquals('foo-bar', Casing::toKebab('foo bar'));
		static::assertEquals('foo-bar', Casing::toKebab('Foo Bar'));
		static::assertEquals('foo-bar', Casing::toKebab('FOO BAR'));
		static::assertEquals('foo-bar', Casing::toKebab('foo-bar'));
		static::assertEquals('foo-bar', Casing::toKebab('Foo-Bar'));
		static::assertEquals('foo-bar', Casing::toKebab('FOO-BAR'));
		static::assertEquals('foo-bar', Casing::toKebab('foo_bar'));
		static::assertEquals('foo-bar', Casing::toKebab('Foo_Bar'));
		static::assertEquals('foo-bar', Casing::toKebab('FOO_BAR'));
	}

	public function testToHttpHeader(): void
	{
		static::assertEquals('Foo', Casing::toHttpHeader('foo'));
		static::assertEquals('Foo', Casing::toHttpHeader('Foo'));
		static::assertEquals('Foobar', Casing::toHttpHeader('FooBar'));
		static::assertEquals('Foo-Bar', Casing::toHttpHeader('foo bar'));
		static::assertEquals('Foo-Bar', Casing::toHttpHeader('Foo Bar'));
		static::assertEquals('Foo-Bar', Casing::toHttpHeader('FOO BAR'));
		static::assertEquals('Foo-Bar', Casing::toHttpHeader('foo-bar'));
		static::assertEquals('Foo-Bar', Casing::toHttpHeader('Foo-Bar'));
		static::assertEquals('Foo-Bar', Casing::toHttpHeader('FOO-BAR'));
		static::assertEquals('Foo-Bar', Casing::toHttpHeader('foo_bar'));
		static::assertEquals('Foo-Bar', Casing::toHttpHeader('Foo_Bar'));
		static::assertEquals('Foo-Bar', Casing::toHttpHeader('FOO_BAR'));
	}

	public function testToCobol(): void
	{
		static::assertEquals('FOO', Casing::toCobol('foo'));
		static::assertEquals('FOO', Casing::toCobol('Foo'));
		static::assertEquals('FOOBAR', Casing::toCobol('FooBar'));
		static::assertEquals('FOO-BAR', Casing::toCobol('foo bar'));
		static::assertEquals('FOO-BAR', Casing::toCobol('Foo Bar'));
		static::assertEquals('FOO-BAR', Casing::toCobol('FOO BAR'));
		static::assertEquals('FOO-BAR', Casing::toCobol('foo-bar'));
		static::assertEquals('FOO-BAR', Casing::toCobol('Foo-Bar'));
		static::assertEquals('FOO-BAR', Casing::toCobol('FOO-BAR'));
		static::assertEquals('FOO-BAR', Casing::toCobol('foo_bar'));
		static::assertEquals('FOO-BAR', Casing::toCobol('Foo_Bar'));
		static::assertEquals('FOO-BAR', Casing::toCobol('FOO_BAR'));
	}

	public function testToPascal(): void
	{
		static::assertEquals('Foo', Casing::toPascal('foo'));
		static::assertEquals('Foo', Casing::toPascal('Foo'));
		static::assertEquals('Foobar', Casing::toPascal('FooBar'));
		static::assertEquals('FooBar', Casing::toPascal('foo bar'));
		static::assertEquals('FooBar', Casing::toPascal('Foo Bar'));
		static::assertEquals('FooBar', Casing::toPascal('FOO BAR'));
		static::assertEquals('FooBar', Casing::toPascal('foo-bar'));
		static::assertEquals('FooBar', Casing::toPascal('Foo-Bar'));
		static::assertEquals('FooBar', Casing::toPascal('FOO-BAR'));
		static::assertEquals('FooBar', Casing::toPascal('foo_bar'));
		static::assertEquals('FooBar', Casing::toPascal('Foo_Bar'));
		static::assertEquals('FooBar', Casing::toPascal('FOO_BAR'));
	}
}
