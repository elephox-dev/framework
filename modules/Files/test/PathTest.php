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
	public function joinDataProvider(): iterable
	{
		yield [['/foo', 'bar', 'baz'], '/foo' . DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR . 'baz'];
		yield [['foo'], 'foo'];
		yield [['C:\\foo\\bar', 'test/var/x', 'deep/', 'folder'], 'C:\\foo\\bar' . DIRECTORY_SEPARATOR . 'test/var/x' . DIRECTORY_SEPARATOR . 'deep' . DIRECTORY_SEPARATOR . 'folder'];
	}

	/**
	 * @dataProvider joinDataProvider
	 *
	 * @param array $parts
	 * @param string $targetPath
	 */
	public function testJoin(array $parts, string $targetPath): void
	{
		$path = Path::join(...$parts);
		static::assertEquals($path, $targetPath);
	}

	public function rootDataProvider(): iterable
	{
		yield ['/long/path/to/test', false];
		yield ['/', true];
		yield ['C:\\Windows\\System32', false];
		yield ['C:\\', true];
	}

	/**
	 * @dataProvider rootDataProvider
	 *
	 * @param string $path
	 * @param bool $isRoot
	 */
	public function testIsRoot(string $path, bool $isRoot): void
	{
		static::assertEquals($isRoot, Path::isRoot($path));
	}

	public function rootedDataProvider(): iterable
	{
		yield ['/', true];
		yield ['C:\\', true];
		yield ['/long/path/to/test', true];
		yield ['C:\\Windows\\System32', true];
		yield ['../test/relative', false];
		yield ['in/this/folder', false];
		yield ['..\\test\\relative', false];
		yield ['in\\this\\folder', false];
	}

	/**
	 * @dataProvider rootedDataProvider
	 *
	 * @param string $path
	 * @param bool $result
	 */
	public function testIsRooted(string $path, bool $result): void
	{
		static::assertEquals($result, Path::isRooted($path), "Path: $path");
	}

	public function relativeToProvider(): iterable
	{
		yield ['/var/www/test', '/var/tmp/db', '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'db'];
		yield ['C:\\data', 'C:\\data\\test\\more', 'test' . DIRECTORY_SEPARATOR . 'more'];
		yield ['C:\\data\\test\\more', 'C:\\data', '..' . DIRECTORY_SEPARATOR . '..'];
		yield ['C:\\test\\file.tmp', 'C:\\tmp\\new\\test.tmp', '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'new' . DIRECTORY_SEPARATOR . 'test.tmp'];
	}

	/**
	 * @dataProvider relativeToProvider
	 *
	 * @param string $pathA
	 * @param string $pathB
	 * @param string $result
	 */
	public function testRelativeTo(string $pathA, string $pathB, string $result): void
	{
		static::assertEquals($result, Path::relativeTo($pathA, $pathB));
	}
}
