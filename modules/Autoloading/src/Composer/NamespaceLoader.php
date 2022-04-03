<?php
declare(strict_types=1);

namespace Elephox\Autoloading\Composer;

use Elephox\Collection\ArrayList;
use Elephox\Collection\ArrayMap;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Files\Contract\Directory as DirectoryContract;
use Elephox\Files\Directory;
use Elephox\OOR\Regex;
use Elephox\Autoloading\Composer\Contract\ComposerAutoloaderInit;
use Elephox\Autoloading\Composer\Contract\ComposerClassLoader;
use RuntimeException;

class NamespaceLoader
{
	/**
	 * @return ComposerClassLoader
	 */
	private static function getClassLoader(): object
	{
		/** @var null|class-string<ComposerAutoloaderInit> $autoloaderClassName */
		$autoloaderClassName = null;
		foreach (get_declared_classes() as $class) {
			if (!str_starts_with($class, 'ComposerAutoloaderInit')) {
				continue;
			}

			$autoloaderClassName = $class;

			break;
		}

		if ($autoloaderClassName === null) {
			throw new RuntimeException('Could not find ComposerAutoloaderInit class. Did you install the dependencies using composer?');
		}

		/** @var ComposerClassLoader */
		return call_user_func([$autoloaderClassName, 'getLoader']);
	}

	/**
	 * @param string $namespace
	 * @param callable(class-string): void $callback
	 */
	public static function iterateNamespace(string $namespace, callable $callback): void
	{
		$classLoader = self::getClassLoader();
		$prefixDirMap = ArrayMap::from($classLoader->getPrefixesPsr4())
			->select(fn(array $dirs): GenericKeyedEnumerable => ArrayList::from($dirs)
				->select(fn(string $dir): DirectoryContract => new Directory($dir))
			);
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
				self::iterateClassesRecursive($root, $parts, $partsUsed, $dir, $classLoader, $callback);
				assert($partsUsed->isEmpty());
			}
		}
	}

	/**
	 * @param string $rootNs
	 * @param ArrayList<string> $nsParts
	 * @param ArrayList<string> $nsPartsUsed
	 * @param DirectoryContract $directory
	 * @param ComposerClassLoader $classLoader
	 * @param callable(class-string): void $callback
	 * @param int $depth
	 *
	 * @noinspection PhpDocSignatureInspection
	 */
	private static function iterateClassesRecursive(string $rootNs, ArrayList $nsParts, ArrayList $nsPartsUsed, DirectoryContract $directory, object $classLoader, callable $callback, int $depth = 0): void
	{
		if ($depth > 10) {
			throw new RuntimeException("Recursion limit exceeded. Please choose a more specific namespace.");
		}

		$lastPart = $nsParts->shift();
		$nsPartsUsed->add($lastPart);
		foreach ($directory->getDirectories() as $dir) {
			if ($dir->getName() !== $lastPart) {
				continue;
			}

			self::iterateClassesRecursive($rootNs, $nsParts, $nsPartsUsed, $dir, $classLoader, $callback, $depth + 1);
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
				$fqcn = $rootNs . "\\" . implode("\\", $nsPartsUsed->toList()) . $className;

				$classLoader->loadClass($fqcn);

				$callback($fqcn);
			}
		}

		$nsParts->unshift($nsPartsUsed->pop());
	}
}
