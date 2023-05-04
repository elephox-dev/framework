<?php
declare(strict_types=1);

namespace Elephox\Autoloading;

use Elephox\Autoloading\Composer\GlobalNamespaceLoader;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Autoloading\Composer\GlobalNamespaceLoader
 * @covers \Elephox\Autoloading\Composer\GlobalClassLoaderProvider
 * @covers \Elephox\Autoloading\Composer\NamespaceIterator
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Collection\IteratorProvider
 * @covers \Elephox\Collection\Iterator\EagerCachingIterator
 * @covers \Elephox\Collection\Iterator\SelectIterator
 * @covers \Elephox\Files\AbstractFilesystemNode
 * @covers \Elephox\Files\Directory
 * @covers \Elephox\Files\File
 * @covers \Elephox\Files\Path
 * @covers \Elephox\OOR\Regex
 *
 * @uses \Elephox\Collection\IsArrayEnumerable
 *
 * @internal
 */
final class GlobalNamespaceLoaderTest extends TestCase
{
	public function testIterateNamespace(): void
	{
		$generator = GlobalNamespaceLoader::iterateNamespace('Elephox\\Autoloading\\Namespaces\\');

		self::assertSame(
			[
				'Elephox\Autoloading\Namespaces\A\B\C\MoreNestedClass',
				'Elephox\Autoloading\Namespaces\A\B\NestedClass',
				'Elephox\Autoloading\Namespaces\A\SomeClass',
				'Elephox\Autoloading\Namespaces\A\SomeOtherClass',
				'Elephox\Autoloading\Namespaces\D\AnotherDifferentClass',
				'Elephox\Autoloading\Namespaces\D\SomeDifferentClass',
			],
			iterator_to_array($generator),
		);
	}
}
