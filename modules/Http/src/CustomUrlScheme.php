<?php
declare(strict_types=1);

namespace Elephox\Http;

class CustomUrlScheme implements Contract\UrlScheme
{
	public function __construct(
		private string $scheme,
		private ?int $defaultPort = null,
	) {
	}

	public function getScheme(): string
	{
		return $this->scheme;
	}

	public function getDefaultPort(): ?int
	{
		return $this->defaultPort;
	}
}
