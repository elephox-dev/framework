<?php
declare(strict_types=1);

namespace Elephox\Support;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
class CustomMimeType implements Contract\MimeType
{
	/**
	 * @param non-empty-string $value
	 */
	#[Pure]
	public function __construct(
		private string $value
	)
	{
	}

	#[Pure]
	public function getValue(): string
	{
		return $this->value;
	}
}
