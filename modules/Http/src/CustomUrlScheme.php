<?php
declare(strict_types=1);

namespace Elephox\Http;

use JetBrains\PhpStorm\Pure;

class CustomUrlScheme implements Contract\UrlScheme
{
	public function __construct(
		private string $scheme,
		private ?int $defaultPort = null,
	) {
	}

	#[Pure] public function getScheme(): string
	{
		return $this->scheme;
	}

	#[Pure] public function getDefaultPort(): ?int
	{
		return $this->defaultPort;
	}
}
