<?php
declare(strict_types=1);

namespace Elephox\Http;

class CustomRequestMethod implements Contract\RequestMethod
{
	public function __construct(
		private readonly string $value,
		private readonly bool $canHaveBody = true,
	) {
	}

	public function getValue(): string
	{
		return $this->value;
	}

	public function canHaveBody(): bool
	{
		return $this->canHaveBody;
	}
}
