<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

interface MimeType
{
	/**
	 * @return non-empty-string
	 */
	public function getValue(): string;
}
