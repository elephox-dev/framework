<?php
declare(strict_types=1);

namespace Elephox\Http;

use JetBrains\PhpStorm\Pure;

readonly class CustomRequestMethod implements Contract\RequestMethod
{
	/**
	 * @param non-empty-string $value
	 */
	public function __construct(
		private string $value,
		private bool $canHaveBody = true,
	) {
	}

	/**
	 * @return non-empty-string
	 */
	#[Pure]
	public function getValue(): string
	{
		return $this->value;
	}

	#[Pure]
	public function canHaveBody(): bool
	{
		return $this->canHaveBody;
	}
}
