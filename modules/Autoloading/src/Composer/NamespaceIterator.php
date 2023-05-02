<?php
declare(strict_types=1);

namespace Elephox\Autoloading\Composer;

use Composer\Autoload\ClassLoader;
use Elephox\Collection\ArrayList;
use Elephox\Files\Contract\Directory as DirectoryContract;
use Elephox\Files\Directory;
use Elephox\OOR\Regex;
use Iterator;
use RuntimeException;

/**
 * @implements Iterator<int, class-string>
 */
final readonly class NamespaceIterator implements Iterator
{
	/**
	 * @var ArrayList<class-string>
	 */
	private ArrayList $classes;

	public function __construct(
		private ClassLoader $classLoader,
		private string $namespace,
	) {
		/** @var ArrayList<class-string> */
		$this->classes = new ArrayList();
	}

	public function current(): mixed
	{
		return $this->classes->current();
	}

	public function next(): void
	{
		$this->classes->next();
	}

	public function key(): ?int
	{
		return $this->classes->key();
	}

	public function valid(): bool
	{
		return $this->classes->key() !== null;
	}

	public function rewind(): void
	{
		$this->classes->clear();

		$parts = Regex::split('/\\\\/', rtrim($this->namespace, '\\') . '\\');

		foreach ($this->classLoader->getPrefixesPsr4() as $nsPrefix => $dirs) {
			if (!str_starts_with($this->namespace, $nsPrefix) && !str_starts_with($nsPrefix, $this->namespace)) {
				continue;
			}

			$root = $parts->shift();
			while ($root !== rtrim($nsPrefix, '\\')) {
				$root .= '\\' . $parts->shift();
			}

			foreach ($dirs as $dirName) {
				$directory = new Directory($dirName);
				if (!$directory->exists()) {
					continue;
				}

				$partsUsed = new ArrayList();

				$this->classes->addAll($this->iterateClassesRecursive($root, $parts, $partsUsed, $directory));

				assert($partsUsed->isEmpty());
			}
		}
	}

	/**
	 * @param ArrayList<string> $nsParts
	 * @param ArrayList<string> $nsPartsUsed
	 * @param string $rootNs
	 * @param DirectoryContract $directory
	 * @param int $depth
	 *
	 * @return iterable<int, class-string>
	 */
	private function iterateClassesRecursive(string $rootNs, ArrayList $nsParts, ArrayList $nsPartsUsed, DirectoryContract $directory, int $depth = 0): iterable
	{
		if ($depth > 10) {
			throw new RuntimeException('Recursion limit exceeded. Please choose a more specific namespace.');
		}

		$lastPart = $nsParts->shift();
		$nsPartsUsed->add($lastPart);
		foreach ($directory->directories() as $dir) {
			if ($dir->name() !== $lastPart) {
				continue;
			}

			yield from $this->iterateClassesRecursive($rootNs, $nsParts, $nsPartsUsed, $dir, $depth + 1);
		}

		if ($lastPart === '') {
			foreach ($directory->files() as $file) {
				$filename = $file->name();
				if (!str_ends_with($filename, '.php')) {
					continue;
				}

				$className = substr($filename, 0, -4);

				/**
				 * @var class-string $fqcn
				 */
				$fqcn = $rootNs . '\\' . implode('\\', $nsPartsUsed->toList()) . $className;

				if (!class_exists($fqcn, false)) {
					$this->classLoader->loadClass($fqcn);
				}

				yield $fqcn;
			}
		}

		$nsParts->unshift($nsPartsUsed->pop());
	}
}
