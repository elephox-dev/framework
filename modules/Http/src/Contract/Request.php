<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\RequestMethod;

interface Request extends Message
{
	public function getRequestMethod(): RequestMethod;

	public function getUrl(): Url;

	public function with(): RequestBuilder;
}
