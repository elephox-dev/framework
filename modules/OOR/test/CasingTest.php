<?php

namespace Elephox\OOR;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\OOR\Casing
 */
class CasingTest extends TestCase
{
	public function testToLower(): void
	{
		self::assertEquals('foo', Casing::toLower('Foo'));
		self::assertEquals('foo', Casing::toLower('foo'));
		self::assertEquals('foobar', Casing::toLower('FooBar'));
		self::assertEquals('foo bar', Casing::toLower('foO Bar'));
		self::assertEquals('foo-bar', Casing::toLower('foO-Bar'));
		self::assertEquals('foo_bar', Casing::toLower('FOO_BAR'));
	}

	public function testToUpper(): void
	{
		self::assertEquals('FOO', Casing::toUpper('Foo'));
		self::assertEquals('FOO', Casing::toUpper('foo'));
		self::assertEquals('FOOBAR', Casing::toUpper('FooBar'));
		self::assertEquals('FOO BAR', Casing::toUpper('foO Bar'));
		self::assertEquals('FOO-BAR', Casing::toUpper('foO-Bar'));
		self::assertEquals('FOO_BAR', Casing::toUpper('foo_bar'));
	}

	public function testToTitle(): void
	{
		self::assertEquals('Foo', Casing::toTitle('foo'));
		self::assertEquals('Foo', Casing::toTitle('Foo'));
		self::assertEquals('Foo Bar', Casing::toTitle('foo bar'));
		self::assertEquals('Foo Bar', Casing::toTitle('Foo Bar'));
		self::assertEquals('Foo Bar', Casing::toTitle('FOO BAR'));
		self::assertEquals('Foo-Bar', Casing::toTitle('foo-bar'));
		self::assertEquals('Foo-Bar', Casing::toTitle('Foo-Bar'));
		self::assertEquals('Foo-Bar', Casing::toTitle('FOO-BAR'));
		self::assertEquals('Foo_Bar', Casing::toTitle('foo_bar'));
		self::assertEquals('Foo_Bar', Casing::toTitle('Foo_Bar'));
		self::assertEquals('Foo_Bar', Casing::toTitle('FOO_BAR'));
	}

	public function testReplaceDelimiters(): void
	{
		self::assertEquals('foo\nbar', Casing::replaceDelimiters('foo bar', '\n'));
		self::assertEquals('example.com', Casing::replaceDelimiters('example.com', ' '));
	}

	public function testToCamel(): void
	{
		self::assertEquals('foo', Casing::toCamel('foo'));
		self::assertEquals('foo', Casing::toCamel('Foo'));
		self::assertEquals('fooBar', Casing::toCamel('foo bar'));
		self::assertEquals('fooBar', Casing::toCamel('Foo Bar'));
		self::assertEquals('fooBar', Casing::toCamel('FOO BAR'));
		self::assertEquals('fooBar', Casing::toCamel('foo-bar'));
		self::assertEquals('fooBar', Casing::toCamel('Foo-Bar'));
		self::assertEquals('fooBar', Casing::toCamel('FOO-BAR'));
		self::assertEquals('fooBar', Casing::toCamel('foo_bar'));
		self::assertEquals('fooBar', Casing::toCamel('Foo_Bar'));
		self::assertEquals('fooBar', Casing::toCamel('FOO_BAR'));
	}

	public function testToSnake(): void
	{
		self::assertEquals('foo', Casing::toSnake('foo'));
		self::assertEquals('foo', Casing::toSnake('Foo'));
		self::assertEquals('foo_bar', Casing::toSnake('foo bar'));
		self::assertEquals('foo_bar', Casing::toSnake('Foo Bar'));
		self::assertEquals('foo_bar', Casing::toSnake('FOO BAR'));
		self::assertEquals('foo_bar', Casing::toSnake('foo-bar'));
		self::assertEquals('foo_bar', Casing::toSnake('Foo-Bar'));
		self::assertEquals('foo_bar', Casing::toSnake('FOO-BAR'));
		self::assertEquals('foo_bar', Casing::toSnake('foo_bar'));
		self::assertEquals('foo_bar', Casing::toSnake('Foo_Bar'));
		self::assertEquals('foo_bar', Casing::toSnake('FOO_BAR'));
	}

	public function testToKebab(): void
	{
		self::assertEquals('foo', Casing::toKebab('foo'));
		self::assertEquals('foo', Casing::toKebab('Foo'));
		self::assertEquals('foo-bar', Casing::toKebab('foo bar'));
		self::assertEquals('foo-bar', Casing::toKebab('Foo Bar'));
		self::assertEquals('foo-bar', Casing::toKebab('FOO BAR'));
		self::assertEquals('foo-bar', Casing::toKebab('foo-bar'));
		self::assertEquals('foo-bar', Casing::toKebab('Foo-Bar'));
		self::assertEquals('foo-bar', Casing::toKebab('FOO-BAR'));
		self::assertEquals('foo-bar', Casing::toKebab('foo_bar'));
		self::assertEquals('foo-bar', Casing::toKebab('Foo_Bar'));
		self::assertEquals('foo-bar', Casing::toKebab('FOO_BAR'));
	}

	public function testToHttpHeader(): void
	{
		self::assertEquals('Foo', Casing::toHttpHeader('foo'));
		self::assertEquals('Foo', Casing::toHttpHeader('Foo'));
		self::assertEquals('Foo-Bar', Casing::toHttpHeader('foo bar'));
		self::assertEquals('Foo-Bar', Casing::toHttpHeader('Foo Bar'));
		self::assertEquals('Foo-Bar', Casing::toHttpHeader('FOO BAR'));
		self::assertEquals('Foo-Bar', Casing::toHttpHeader('foo-bar'));
		self::assertEquals('Foo-Bar', Casing::toHttpHeader('Foo-Bar'));
		self::assertEquals('Foo-Bar', Casing::toHttpHeader('FOO-BAR'));
		self::assertEquals('Foo-Bar', Casing::toHttpHeader('foo_bar'));
		self::assertEquals('Foo-Bar', Casing::toHttpHeader('Foo_Bar'));
		self::assertEquals('Foo-Bar', Casing::toHttpHeader('FOO_BAR'));
	}

	public function testToCobol(): void
	{
		self::assertEquals('FOO', Casing::toCobol('foo'));
		self::assertEquals('FOO', Casing::toCobol('Foo'));
		self::assertEquals('FOO-BAR', Casing::toCobol('foo bar'));
		self::assertEquals('FOO-BAR', Casing::toCobol('Foo Bar'));
		self::assertEquals('FOO-BAR', Casing::toCobol('FOO BAR'));
		self::assertEquals('FOO-BAR', Casing::toCobol('foo-bar'));
		self::assertEquals('FOO-BAR', Casing::toCobol('Foo-Bar'));
		self::assertEquals('FOO-BAR', Casing::toCobol('FOO-BAR'));
		self::assertEquals('FOO-BAR', Casing::toCobol('foo_bar'));
		self::assertEquals('FOO-BAR', Casing::toCobol('Foo_Bar'));
		self::assertEquals('FOO-BAR', Casing::toCobol('FOO_BAR'));
	}

	public function testToPascal(): void
	{
		self::assertEquals('Foo', Casing::toPascal('foo'));
		self::assertEquals('Foo', Casing::toPascal('Foo'));
		self::assertEquals('FooBar', Casing::toPascal('foo bar'));
		self::assertEquals('FooBar', Casing::toPascal('Foo Bar'));
		self::assertEquals('FooBar', Casing::toPascal('FOO BAR'));
		self::assertEquals('FooBar', Casing::toPascal('foo-bar'));
		self::assertEquals('FooBar', Casing::toPascal('Foo-Bar'));
		self::assertEquals('FooBar', Casing::toPascal('FOO-BAR'));
		self::assertEquals('FooBar', Casing::toPascal('foo_bar'));
		self::assertEquals('FooBar', Casing::toPascal('Foo_Bar'));
		self::assertEquals('FooBar', Casing::toPascal('FOO_BAR'));
	}
}
