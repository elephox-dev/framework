<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use JetBrains\PhpStorm\Pure;

interface Request extends HttpMessage
{
	public function getHeaderMap(): RequestHeaderMap;

	#[Pure] public function getRequestMethod(): RequestMethod;

	public function withRequestMethod(RequestMethod $method): static;

	public function withUrl(Url $url, bool $preserveHost = false): static;

	#[Pure] public function getUrl(): Url;
}
