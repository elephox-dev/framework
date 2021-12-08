<?php
declare(strict_types=1);

namespace Elephox\Http;

use JetBrains\PhpStorm\Pure;

/**
 * @psalm-consistent-constructor
 */
class CustomRequestMethod implements Contract\RequestMethod
{
	/**
	 * @param non-empty-string $method
	 */
	public function __construct(
		private string $method,
		private bool $canHaveBody = true
	)
	{
	}

	#[Pure] public function getValue(): string
	{
		return $this->method;
	}

	#[Pure] public function canHaveBody(): bool
	{
		return $this->canHaveBody;
	}

	#[Pure] public function copy(): static
	{
		return new static($this->method, $this->canHaveBody);
	}
}
