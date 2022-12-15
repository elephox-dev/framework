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
		static::assertSame($path, $targetPath);
	}

	public function rootDataProvider(): iterable
	{
		yield ['/long/path/to/test', false];
		yield ['/', true];
		yield ['C:\\Windows\\System32', false];
		yield ['C:\\', true];
		yield ['\\\\nas', true];
		yield ['\\\\nas\\', true];
		yield ['\\\\nas\\file.txt', false];
	}

	/**
	 * @dataProvider rootDataProvider
	 *
	 * @param string $path
	 * @param bool $isRoot
	 */
	public function testIsRoot(string $path, bool $isRoot): void
	{
		static::assertSame($isRoot, Path::isRoot($path));
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
		yield ['\\\\nas\\file.txt', true];
	}

	/**
	 * @dataProvider rootedDataProvider
	 *
	 * @param string $path
	 * @param bool $result
	 */
	public function testIsRooted(string $path, bool $result): void
	{
		static::assertSame($result, Path::isRooted($path), "Path: $path");
	}

	public function relativeToProvider(): iterable
	{
		yield ['/var/www/test', '/var/tmp/db', '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'db'];
		yield ['C:\\data', 'C:\\data\\test\\more', '.' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'more'];
		yield ['C:\\data\\test\\more', 'C:\\data', '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR];
		yield ['C:\\test\\file.tmp', 'C:\\tmp\\new\\test.tmp', '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'new' . DIRECTORY_SEPARATOR . 'test.tmp'];
		yield ['C:\\tmp\\folder\\', 'C:\\tmp\\folder\\test.tmp', '.' . DIRECTORY_SEPARATOR . 'test.tmp'];
		yield ['/tmp/a/b/', '/tmp/a/test.tmp', '..' . DIRECTORY_SEPARATOR . 'test.tmp'];
		yield ['/tmp/a/b', '/tmp/a/test.tmp', '..' . DIRECTORY_SEPARATOR . 'test.tmp'];
		yield ['C:\\a\\b\\c', 'C:\\a\\b', '..' . DIRECTORY_SEPARATOR];
		yield ['C:\\a\\b\\c', 'C:\\a\\b\\', '..' . DIRECTORY_SEPARATOR];
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
		static::assertSame($result, Path::relativeTo($pathA, $pathB));
	}
}
