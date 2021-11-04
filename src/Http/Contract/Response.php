<?php

namespace Philly\Http\Contract;

use Philly\Http\HeaderMap;
use Philly\Http\ResponseCode;

interface Response
{
	public function getHeaders(): HeaderMap;

	public function setCode(ResponseCode $code): void;

	public function getCode(): ResponseCode;

	public function setContent(string $content): void;

	public function getContent(): string;
}
