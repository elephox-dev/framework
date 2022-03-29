<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Contract;

interface ComposerClassLoader
{
	/**
	 * @return array<string, array<int, string>>
	 */
	public function getPrefixesPsr4(): array;

	/**
	 * @param class-string $class
	 * @return null|true
	 */
	public function loadClass(string $class): ?bool;
}
