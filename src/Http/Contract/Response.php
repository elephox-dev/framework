<?php

namespace Philly\Http\Contract;

use Philly\Http\HeaderMap;
use Philly\Http\ResponseCode;

interface Response
{
	public function setUri(string $uri): void;

	public function getUri(): string;

	public function getHeaders(): HeaderMap;

	public function setCode(ResponseCode $code): void;

	public function getCode(): ResponseCode;
}
