<?php
declare(strict_types=1);

namespace Elephox\Support;

class CustomMimeType implements Contract\MimeType
{
	/**
	 * @param non-empty-string $value
	 */
	public function __construct(
		private string $value
	)
	{
	}

	public function getValue(): string
	{
		return $this->value;
	}
}
