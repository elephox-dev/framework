<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use JetBrains\PhpStorm\Pure;

interface UrlScheme
{
	#[Pure] public function getScheme(): string;

	#[Pure] public function getDefaultPort(): ?int;
}
