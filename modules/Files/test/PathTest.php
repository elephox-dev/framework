<?php
declare(strict_types=1);

namespace Elephox\Files;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Files\Path
 */
class PathTest extends TestCase
{
	public function testJoin(): void
	{
		$path1 = Path::join('/foo', 'bar', 'baz');
		self::assertEquals('/foo' . DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR . 'baz', $path1);

		$path2 = Path::join('foo');
		self::assertEquals('foo', $path2);
	}
}
