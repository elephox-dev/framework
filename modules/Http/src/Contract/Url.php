<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\UrlScheme;
use Elephox\Support\Contract\ArrayConvertible;
use Psr\Http\Message\UriInterface;
use Stringable;

interface Url extends Stringable, ArrayConvertible, UriInterface
{
	public function getScheme(): string;

	public function getUrlScheme(): ?UrlScheme;

	public function getAuthority(): string;

	public function getUserInfo(): string;

	public function getUsername(): string;

	public function getPassword(): string;

	public function getHost(): string;

	public function getPort(): ?int;

	public function getPath(): string;

	public function getQuery(): string;

	public function getFragment(): string;

	public function withScheme($scheme): static;

	public function withUserInfo($user, $password = null): static;

	public function withHost($host): static;

	public function withPort($port): static;

	public function withPath($path): static;

	public function withQuery($query): static;

	public function withFragment($fragment): static;

	public function withUrlScheme(UrlScheme $scheme): static;
}
