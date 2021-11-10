<?php
declare(strict_types=1);

namespace Philly\Http\Contract;

use Philly\Http\RequestMethod;

interface Request
{
	public function getUrl(): Url;

	public function getMethod(): RequestMethod;

	public function getHeaders(): ReadonlyHeaderMap;
}
