<?php
declare(strict_types=1);

namespace Elephox\Autoloading\Composer;

use Composer\Autoload\ClassLoader;
use Elephox\Collection\ArrayList;
use Elephox\Files\Contract\File;
use Elephox\Files\Directory;
use Elephox\OOR\Regex;
use Iterator;

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

		foreach ($this->classLoader->getPrefixesPsr4() as $nsPrefix => $dirs) {
			assert(str_ends_with($nsPrefix, '\\'), 'Namespace prefix must end with "\\": ' . $nsPrefix);

			foreach ($dirs as $dirName) {
				$directory = new Directory($dirName);
				if (!$directory->exists()) {
					continue;
				}

				/** @var File $file */
				foreach ($directory->recurseFiles() as $file) {
					if ($file->extension() !== 'php') {
						continue;
					}

					$relativePath = $directory->relativePathTo($file);

					assert(str_starts_with($relativePath, './') && str_ends_with($relativePath, '.php'), 'Relative path must start with "./" and end with ".php": ' . $relativePath);

					$namespaceRelativePath = substr($relativePath, 2, -4); // cut off './' and '.php'
					$namespaceRelativeParts = Regex::split('#[/\\\\]#', $namespaceRelativePath);

					$className = $nsPrefix . $namespaceRelativeParts->implode('\\');
					if (!str_starts_with($className, $this->namespace)) {
						continue;
					}

					if (class_exists($className, false)) {
						$this->classes->add($className);
					} else {
						if ($this->classLoader->loadClass($className) === true) {
							$this->classes->add($className);
						}
					}
				}
			}
		}
	}
}
