<?php
declare(strict_types=1);

namespace Elephox\Http;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

/**
 * @psalm-consistent-constructor
 */
#[Immutable]
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

	public function getValue(): string
	{
		return $this->method;
	}

	public function canHaveBody(): bool
	{
		return $this->canHaveBody;
	}

	public function copy(): static
	{
		return new static($this->method, $this->canHaveBody);
	}
}
