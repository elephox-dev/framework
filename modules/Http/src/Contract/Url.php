<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\UrlScheme;
use Elephox\Support\Contract\ArrayConvertible;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\UriInterface;
use Stringable;

interface Url extends Stringable, ArrayConvertible, UriInterface
{
	#[Pure] public function getScheme(): string;

	#[Pure] public function getUrlScheme(): ?UrlScheme;

	#[Pure] public function getAuthority(): string;

	#[Pure] public function getUserInfo(): string;

	#[Pure] public function getUsername(): string;

	#[Pure] public function getPassword(): string;

	#[Pure] public function getHost(): string;

	#[Pure] public function getPort(): ?int;

	#[Pure] public function getPath(): string;

	#[Pure] public function getQuery(): string;

	#[Pure] public function getFragment(): string;

	#[Pure] public function getOriginal(): string;

	public function withScheme($scheme): static;

	public function withUserInfo($user, $password = null): static;

	public function withHost($host): static;

	public function withPort($port): static;

	public function withPath($path): static;

	public function withQuery($query): static;

	public function withFragment($fragment): static;

	public function withUrlScheme(UrlScheme $scheme): static;
}
