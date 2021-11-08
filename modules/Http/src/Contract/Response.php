<?php
declare(strict_types=1);

namespace Philly\Http\Contract;

use Philly\Http\ResponseCode;

interface Response
{
	public function getHeaders(): HeaderMap;

	public function setCode(ResponseCode $code): void;

	public function getCode(): ResponseCode;

	public function setContent(string $content): void;

	public function getContent(): string;
}
