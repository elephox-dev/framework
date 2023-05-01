<?php
declare(strict_types=1);

namespace Elephox\Autoloading\Composer;

use Composer\Autoload\ClassLoader;
use Elephox\Collection\ArrayList;
use Elephox\Collection\ArrayMap;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Files\Contract\Directory as DirectoryContract;
use Elephox\Files\Directory;
use Elephox\OOR\Regex;
use Elephox\Autoloading\Composer\Contract\ComposerAutoloaderInit;
use RuntimeException;

final class GlobalNamespaceLoader
{
	/**
	 * @param string $namespace
	 *
	 * @psalm-suppress MixedReturnTypeCoercion
	 *
	 * @return iterable<int, class-string>
	 */
	public static function iterateNamespace(string $namespace): iterable
	{
		$provider = GlobalClassLoaderProvider::getLoader();

		yield from new NamespaceIterator($provider, $namespace);
	}
}
