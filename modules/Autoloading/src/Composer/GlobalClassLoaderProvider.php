<?php
declare(strict_types=1);

namespace Elephox\Autoloading\Composer;

use Composer\Autoload\ClassLoader;
use Elephox\Autoloading\Composer\Contract\ComposerAutoloaderInit;

final class GlobalClassLoaderProvider
{
	private static ?ClassLoader $singleton = null;

	public static function getLoader(): ClassLoader
	{
		if (self::$singleton === null) {
			/** @var null|class-string<ComposerAutoloaderInit> $autoloaderInitClassName */
			$autoloaderInitClassName = null;
			foreach (get_declared_classes() as $class) {
				if (str_starts_with($class, 'ComposerAutoloaderInit')) {
					$autoloaderInitClassName = $class;

					break;
				}
			}

			if ($autoloaderInitClassName === null) {
				throw new RuntimeException('Could not find ComposerAutoloaderInit class. Did you install the dependencies using composer?');
			}

			/** @var ClassLoader */
			self::$singleton = call_user_func([$autoloaderInitClassName, 'getLoader']);
		}

		return self::$singleton;
	}
}
