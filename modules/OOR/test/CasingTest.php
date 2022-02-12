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
		self::assertEquals('foo-bar', Casing::toLower('foO-Bar'));
		self::assertEquals('foo_bar', Casing::toLower('FOO_BAR'));
	}

	public function testToUpper(): void
	{
		self::assertEquals('FOO', Casing::toUpper('Foo'));
		self::assertEquals('FOO', Casing::toUpper('foo'));
		self::assertEquals('FOOBAR', Casing::toUpper('FooBar'));
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

	public function testToTitleKebab(): void
	{
		self::assertEquals('Foo', Casing::toTitleKebab('foo'));
		self::assertEquals('Foo', Casing::toTitleKebab('Foo'));
		self::assertEquals('Foo-Bar', Casing::toTitleKebab('foo bar'));
		self::assertEquals('Foo-Bar', Casing::toTitleKebab('Foo Bar'));
		self::assertEquals('Foo-Bar', Casing::toTitleKebab('FOO BAR'));
		self::assertEquals('Foo-Bar', Casing::toTitleKebab('foo-bar'));
		self::assertEquals('Foo-Bar', Casing::toTitleKebab('Foo-Bar'));
		self::assertEquals('Foo-Bar', Casing::toTitleKebab('FOO-BAR'));
		self::assertEquals('Foo-Bar', Casing::toTitleKebab('foo_bar'));
		self::assertEquals('Foo-Bar', Casing::toTitleKebab('Foo_Bar'));
		self::assertEquals('Foo-Bar', Casing::toTitleKebab('FOO_BAR'));
	}
}
