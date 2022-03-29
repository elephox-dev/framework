<?php
declare(strict_types=1);

namespace Elephox\Configuration\Contract;

use JetBrains\PhpStorm\Pure;

interface ConfigurationSource
{
	#[Pure]
	public function build(): ConfigurationProvider;
}
