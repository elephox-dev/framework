<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\Url;

interface UrlBuilder
{
	public function scheme(?UrlScheme $scheme): UrlBuilder;

	public function host(?string $host): UrlBuilder;

	public function port(?int $port): UrlBuilder;

	public function path(string $path): UrlBuilder;

	public function queryMap(?QueryMap $query): UrlBuilder;

	public function fragment(?string $fragment): UrlBuilder;

	public function userInfo(?string $username, ?string $password = null): UrlBuilder;

	public function get(bool $replaceDefaultPort = true): Url;
}
