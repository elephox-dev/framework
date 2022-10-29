<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\Url;

interface UrlBuilder
{
	public function scheme(?UrlScheme $scheme): self;

	public function host(?string $host): self;

	public function port(?int $port): self;

	public function path(string $path): self;

	public function queryMap(?QueryMap $query): self;

	public function fragment(?string $fragment): self;

	public function userInfo(?string $username, ?string $password = null): self;

	public function get(bool $replaceDefaultPort = true): Url;
}
