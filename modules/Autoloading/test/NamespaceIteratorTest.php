<?php
declare(strict_types=1);

namespace Elephox\Autoloading;

use Composer\Autoload\ClassLoader;
use Elephox\Autoloading\Composer\NamespaceIterator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Autoloading\Composer\NamespaceIterator
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Collection\IteratorProvider
 * @covers \Elephox\Collection\Iterator\EagerCachingIterator
 * @covers \Elephox\Collection\Iterator\SelectIterator
 * @covers \Elephox\Files\AbstractFilesystemNode
 * @covers \Elephox\Files\Directory
 * @covers \Elephox\Files\File
 * @covers \Elephox\OOR\Regex
 * @covers \Elephox\Files\Path
 *
 * @uses \Elephox\Collection\IsArrayEnumerable
 * @uses \Elephox\Collection\IsKeyedEnumerable
 *
 * @internal
 */
final class NamespaceIteratorTest extends TestCase
{
	private function getTestClassLoader(): ClassLoader
	{
		$classLoader = new ClassLoader();
		$classLoader->addPsr4('Elephox\\Autoloading\\Namespaces\\A\\', __DIR__ . '/Namespaces/A');
		$classLoader->addPsr4('Elephox\\Autoloading\\Namespaces\\D\\', __DIR__ . '/Namespaces/D');

		// add missing directory
		$classLoader->addPsr4('Elephox\\Autoloading\\Namespaces\\E\\', __DIR__ . '/Namespaces/E');

		return $classLoader;
	}

	public function testIterator(): void
	{
		$psr4Iterator = new NamespaceIterator(
			$this->getTestClassLoader(),
			'Elephox\\Autoloading\\Namespaces\\A\\',
		);

		self::assertSame([
			'Elephox\Autoloading\Namespaces\A\B\C\MoreNestedClass',
			'Elephox\Autoloading\Namespaces\A\B\NestedClass',
			'Elephox\Autoloading\Namespaces\A\SomeClass',
			'Elephox\Autoloading\Namespaces\A\SomeOtherClass',
		], iterator_to_array($psr4Iterator));

		$nestedIterator = new NamespaceIterator(
			$this->getTestClassLoader(),
			'Elephox\\Autoloading\\Namespaces\\A\\B\\',
		);

		self::assertSame([
			'Elephox\Autoloading\Namespaces\A\B\C\MoreNestedClass',
			'Elephox\Autoloading\Namespaces\A\B\NestedClass',
		], iterator_to_array($nestedIterator));

		$multiPsr4Iterator = new NamespaceIterator(
			$this->getTestClassLoader(),
			'Elephox\\Autoloading\\Namespaces\\',
		);

		self::assertSame([
			'Elephox\Autoloading\Namespaces\A\B\C\MoreNestedClass',
			'Elephox\Autoloading\Namespaces\A\B\NestedClass',
			'Elephox\Autoloading\Namespaces\A\SomeClass',
			'Elephox\Autoloading\Namespaces\A\SomeOtherClass',
			'Elephox\Autoloading\Namespaces\D\AnotherDifferentClass',
			'Elephox\Autoloading\Namespaces\D\SomeDifferentClass',
		], iterator_to_array($multiPsr4Iterator));
	}
}
