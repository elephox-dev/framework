<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
interface UrlScheme
{
	#[Pure] public function getScheme(): string;

	#[Pure] public function getDefaultPort(): ?int;
}
