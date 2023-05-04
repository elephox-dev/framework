<?php
declare(strict_types=1);

namespace Elephox\Autoloading;

use Composer\Autoload\ClassLoader;
use Elephox\Autoloading\Composer\GlobalClassLoaderProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Autoloading\Composer\GlobalClassLoaderProvider
 *
 * @internal
 */
final class GlobalClassLoaderProviderTest extends TestCase
{
	public function testGetLoader(): void
	{
		$loader = GlobalClassLoaderProvider::getLoader();

		self::assertInstanceOf(ClassLoader::class, $loader);

		$loader2 = GlobalClassLoaderProvider::getLoader();

		self::assertSame($loader, $loader2);
	}
}
