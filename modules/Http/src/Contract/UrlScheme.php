<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
interface UrlScheme
{
	public function getScheme(): string;

	public function getDefaultPort(): ?int;
}
