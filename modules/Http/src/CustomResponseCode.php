<?php
declare(strict_types=1);

namespace Philly\Http;

class CustomResponseCode implements Contract\ResponseCode
{
	/**
	 * @param non-empty-string $message
	 */
	public function __construct(
		private int $code,
		private string $message
	) {}

	public function getCode(): int
	{
		return $this->code;
	}

	public function getMessage(): string
	{
		return $this->message;
	}
}
