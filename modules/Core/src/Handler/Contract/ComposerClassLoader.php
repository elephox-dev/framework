<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Contract;

interface ComposerClassLoader
{
	/**
	 * @return array<class-string, string>
	 */
	public function getClassMap(): array;

	/**
	 * @param class-string $class
	 * @return null|true
	 */
	public function loadClass(string $class): ?bool;
}
