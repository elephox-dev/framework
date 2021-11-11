<?php

namespace Elephox\Http;

class CustomHeaderName implements Contract\HeaderName
{
	/**
	 * @param non-empty-string $name
	 */
	public function __construct(
		private string $name,
		private bool $canBeDuplicate = true,
		private bool $isOnlyRequest = false,
		private bool $isOnlyResponse = false
	) {}

	public function getValue(): string
	{
		return $this->name;
	}

	public function canBeDuplicate(): bool
	{
		return $this->canBeDuplicate;
	}

	public function isOnlyRequest(): bool
	{
		return $this->isOnlyRequest;
	}

	public function isOnlyResponse(): bool
	{
		return $this->isOnlyResponse;
	}
}
