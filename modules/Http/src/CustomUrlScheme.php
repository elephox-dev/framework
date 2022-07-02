<?php
declare(strict_types=1);

namespace Elephox\Http;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
class CustomUrlScheme implements Contract\UrlScheme
{
	#[Pure]
	public function __construct(
		private readonly string $scheme,
		private readonly ?int $defaultPort = null,
	) {
	}

	#[Pure]
	public function getScheme(): string
	{
		return $this->scheme;
	}

	#[Pure]
	public function getDefaultPort(): ?int
	{
		return $this->defaultPort;
	}
}
