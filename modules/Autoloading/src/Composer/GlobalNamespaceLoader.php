<?php
declare(strict_types=1);

namespace Elephox\Autoloading\Composer;

final class GlobalNamespaceLoader
{
	/**
	 * @param string $namespace
	 *
	 * @return iterable<int, class-string>
	 */
	public static function iterateNamespace(string $namespace): iterable
	{
		$provider = GlobalClassLoaderProvider::getLoader();

		yield from new NamespaceIterator($provider, $namespace);
	}
}
