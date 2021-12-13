<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
interface ResponseCode
{
	public function getCode(): int;

	/**
	 * @return non-empty-string
	 */
	public function getMessage(): string;
}
