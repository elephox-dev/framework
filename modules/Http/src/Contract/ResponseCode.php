<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

interface ResponseCode
{
	public function getCode(): int;

	/**
	 * @return non-empty-string
	 */
	public function getMessage(): string;
}
