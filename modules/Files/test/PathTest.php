<?php
declare(strict_types=1);

namespace Elephox\Files;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Files\Path
 *
 * @internal
 */
class PathTest extends TestCase
{
	public function testJoin(): void
	{
		$path1 = Path::join('/foo', 'bar', 'baz');
		static::assertEquals('/foo' . DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR . 'baz', $path1);

		$path2 = Path::join('foo');
		static::assertEquals('foo', $path2);
	}

	public function testIsRoot(): void
	{
		static::assertFalse(Path::isRoot('/long/path/to/test'));
		static::assertTrue(Path::isRoot('/'));
		static::assertFalse(Path::isRoot('C:\\Windows\\System32'));
		static::assertTrue(Path::isRoot('C:\\'));
	}
}
