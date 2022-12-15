<?php
declare(strict_types=1);

namespace Elephox\Http;

class CustomRequestMethod implements Contract\RequestMethod
{
	/**
	 * @param non-empty-string $value
	 */
	public function __construct(
		private readonly string $value,
		private readonly bool $canHaveBody = true,
	) {
	}

	/**
	 * @return non-empty-string
	 */
	public function getValue(): string
	{
		return $this->value;
	}

	public function canHaveBody(): bool
	{
		return $this->canHaveBody;
	}
}
