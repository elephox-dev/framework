<?php
declare(strict_types=1);

namespace Elephox\Support\Contract;

interface MimeType
{
	/**
	 * @return non-empty-string
	 */
	public function getValue(): string;
}
