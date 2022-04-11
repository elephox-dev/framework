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

class NamespaceLoader
{
	private static ?ClassLoader $classLoader = null;

	private static function getClassLoader(): ClassLoader
	{
		if (self::$classLoader === null) {
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
			self::$classLoader = call_user_func([$autoloaderInitClassName, 'getLoader']);
		}

		return self::$classLoader;
	}

	/**
	 * @param callable(class-string): void $callback
	 * @param string $namespace
	 */
	public static function iterateNamespace(string $namespace, callable $callback): void
	{
		$prefixDirMap = ArrayMap::from(self::getClassLoader()->getPrefixesPsr4())
			->select(
				static fn (array $dirs): GenericKeyedEnumerable => ArrayList::from($dirs)
					->select(static fn (string $dir): DirectoryContract => new Directory($dir)),
			)
		;
		foreach ($prefixDirMap as $nsPrefix => $dirs) {
			if (!str_starts_with($namespace, $nsPrefix) && !str_starts_with($nsPrefix, $namespace)) {
				continue;
			}

			$parts = Regex::split('/\\\\/', rtrim($namespace, '\\') . '\\');
			// remove first element since it is the alias for the directories we are iterating
			$root = $parts->shift();
			while ($root !== rtrim($nsPrefix, '\\')) {
				$root .= '\\' . $parts->shift();
			}

			foreach ($dirs as $dir) {
				/** @var ArrayList<string> $partsUsed */
				$partsUsed = new ArrayList();
				self::iterateClassesRecursive($root, $parts, $partsUsed, $dir, $callback);
				assert($partsUsed->isEmpty());
			}
		}
	}

	/**
	 * @param ArrayList<string> $nsParts
	 * @param ArrayList<string> $nsPartsUsed
	 * @param callable(class-string): void $callback
	 * @param string $rootNs
	 * @param DirectoryContract $directory
	 * @param int $depth
	 */
	private static function iterateClassesRecursive(string $rootNs, ArrayList $nsParts, ArrayList $nsPartsUsed, DirectoryContract $directory, callable $callback, int $depth = 0): void
	{
		if ($depth > 10) {
			throw new RuntimeException('Recursion limit exceeded. Please choose a more specific namespace.');
		}

		$lastPart = $nsParts->shift();
		$nsPartsUsed->add($lastPart);
		foreach ($directory->getDirectories() as $dir) {
			if ($dir->getName() !== $lastPart) {
				continue;
			}

			self::iterateClassesRecursive($rootNs, $nsParts, $nsPartsUsed, $dir, $callback, $depth + 1);
		}

		if ($lastPart === '') {
			foreach ($directory->getFiles() as $file) {
				$filename = $file->getName();
				if (!str_ends_with($filename, '.php')) {
					continue;
				}

				$className = substr($filename, 0, -4);

				/**
				 * @var class-string $fqcn
				 */
				$fqcn = $rootNs . '\\' . implode('\\', $nsPartsUsed->toList()) . $className;

				if (!class_exists($fqcn, false)) {
					self::getClassLoader()->loadClass($fqcn);
				}

				$callback($fqcn);
			}
		}

		$nsParts->unshift($nsPartsUsed->pop());
	}
}
