<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Contract;

interface ComposerClassLoader
{
	/**
	 * @return array<array-key, class-string>
	 */
	public function getClassMap(): array;
}
