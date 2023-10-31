<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

interface Disposable
{
	public function dispose(): void;
}
