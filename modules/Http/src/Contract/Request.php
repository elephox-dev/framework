<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

interface Request
{
	public function getUrl(): Url;

	public function getMethod(): RequestMethod;

	public function getHeaders(): ReadonlyHeaderMap;

	public function getBody(): ?string;

	public function shouldFollowRedirects(): bool;
}
