<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Configuration\ConfigurationPath
 * @covers \Elephox\OOR\Arr
 * @covers \Elephox\OOR\Str
 * @covers \Elephox\OOR\Filter
 *
 * @internal
 */
final class ConfigurationPathTest extends TestCase
{
	public function testGetChildKeys(): void
	{
		self::assertSame(
			['x', 'y'],
			ConfigurationPath::getChildKeys(['foo' => ['a' => ['x' => ['z' => 0], 'y' => false], 'b' => 2], 'bar' => ['c' => 3], 'baz' => ['d' => 4]], 'foo:a')->getSource(),
		);
	}

	public function testAppendKey(): void
	{
		self::assertSame(
			'test:path:appended',
			ConfigurationPath::appendKey('test:path', 'appended')->getSource(),
		);
	}

	public function testGetSectionKey(): void
	{
		self::assertSame(
			'appended',
			ConfigurationPath::getSectionKey('test:path:appended')->getSource(),
		);
	}

	public function testGetSectionKeys(): void
	{
		self::assertSame(
			['foo', 'bar', 'baz'],
			ConfigurationPath::getSectionKeys('foo:bar:baz')->getSource(),
		);
	}
}
