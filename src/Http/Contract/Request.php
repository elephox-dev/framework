<?php

namespace Philly\Http\Contract;

use Philly\Http\RequestMethod;

interface Request
{
	public function getUri(): string;

	public function getMethod(): RequestMethod;

	public function getHeaders(): ReadonlyHeaderMap;
}
