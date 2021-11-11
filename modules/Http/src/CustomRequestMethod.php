<?php
declare(strict_types=1);

namespace Elephox\Http;

class CustomRequestMethod implements Contract\RequestMethod
{
	/**
	 * @param non-empty-string $method
	 */
	public function __construct(
		private string $method,
		private bool $canHaveBody = false
	)
	{
	}

	public function getValue(): string
	{
		return $this->method;
	}

	public function canHaveBody(): bool
	{
		return $this->canHaveBody;
	}
}
