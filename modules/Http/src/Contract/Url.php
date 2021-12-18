<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\ArrayMap;
use Elephox\Http\UrlScheme;
use Elephox\Support\Contract\ArrayConvertible;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use Stringable;

#[Immutable]
interface Url extends Stringable, ArrayConvertible
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

	/**
	 * @return ArrayMap<string, string|array>
	 */
	#[Pure] public function getQueryMap(): ArrayMap;

	#[Pure] public function getFragment(): string;

	#[Pure] public function withScheme(?UrlScheme $scheme): static;

	#[Pure] public function withUserInfo(?string $user, ?string $password = null): static;

	#[Pure] public function withHost(?string $host): static;

	#[Pure] public function withPort(?int $port): static;

	#[Pure] public function withPath(string $path): static;

	#[Pure] public function withQuery(?string $query): static;

	/**
	 * @param ArrayMap<string, string|array> $query
	 */
	#[Pure] public function withQueryMap(ArrayMap $query): static;

	#[Pure] public function withFragment(?string $fragment): static;
}
