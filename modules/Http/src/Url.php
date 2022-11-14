<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\OOR\Casing;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\UriInterface;
use Stringable;

#[Immutable]
class Url implements Stringable, UriInterface
{
	public const Pattern = /** @lang RegExp */ '/^(?<scheme>[^:]*:\/\/|\/\/)?(?:(?:(?<username>[^:@]+)(?::(?<password>[^@]+))?@)?(?<host>[^:\/?#*]+)(?::(?<port>\d+))?)?(?<path>[^?#]*)(?<query>\?[^#]*)?(?<fragment>#.*)?$/';

	public static function fromString(string|Stringable $uri): self
	{
		$builder = new UrlBuilder();

		preg_match(
			self::Pattern,
			(string) $uri,
			$matches,
		);
		/**
		 * @var array{scheme: string, username: string, password: string, host: string, port: string, path: string, query: string, fragment: string} $matches
		 */
		if (str_ends_with($matches['scheme'], '://')) {
			$scheme = substr($matches['scheme'], 0, -3);

			$builder->scheme(UrlScheme::tryFrom($scheme) ?? new CustomUrlScheme($scheme));
		}

		$username = empty($matches['username']) ? null : $matches['username'];
		$password = empty($matches['password']) ? null : $matches['password'];
		$builder->userInfo($username, $password);

		$builder->host(empty($matches['host']) ? null : $matches['host']);
		$builder->port(ctype_digit($matches['port']) ? (int) $matches['port'] : null);

		$path = $matches['path'];
		if (str_contains($path, ' ')) {
			$path = str_replace(' ', '%20', $path);
		}
		$builder->path($path);

		if (array_key_exists('query', $matches) && strlen($matches['query']) > 1 && str_starts_with($matches['query'], '?')) {
			$builder->queryMap(QueryMap::fromString(substr($matches['query'], 1)));
		}

		if (array_key_exists('fragment', $matches) && str_starts_with($matches['fragment'], '#')) {
			$builder->fragment(substr($matches['fragment'], 1));
		}

		return $builder->get();
	}

	#[Pure]
	public function __construct(
		public readonly ?Contract\UrlScheme $scheme,
		public readonly ?string $host,
		public readonly ?int $port,
		public readonly string $path,
		public readonly ?Contract\QueryMap $queryMap,
		public readonly ?string $fragment,
		public readonly ?string $username,
		public readonly ?string $password,
	) {
	}

	#[Pure]
	public function getAuthority(): string
	{
		$authority = $this->host;
		if (empty($authority)) {
			return '';
		}

		$port = $this->port;
		if ($port !== null) {
			$authority .= ":$port";
		}

		$userInfo = $this->getUserInfo();
		if (!empty($userInfo)) {
			$authority = "$userInfo@$authority";
		}

		return $authority;
	}

	#[Pure]
	public function getUserInfo(): string
	{
		if ($this->username === null) {
			return '';
		}

		$userInfo = $this->username;

		if ($this->password !== null) {
			$userInfo .= ":$this->password";
		}

		return $userInfo;
	}

	#[Pure]
	public function __toString(): string
	{
		if ($this->scheme !== null) {
			$uri = $this->scheme->getScheme() . ':';
		} else {
			$uri = '';
		}

		$authority = $this->getAuthority();
		if (!empty($authority)) {
			$uri .= '//' . $authority;

			if (!str_starts_with($this->path, '/')) {
				$uri .= '/';
			}

			$uri .= $this->path;
		} else {
			$uri .= '/' . ltrim($this->path, '/');
		}

		if ($this->queryMap !== null) {
			$uri .= '?' . $this->queryMap;
		}

		if ($this->fragment !== null) {
			$uri .= '#' . $this->fragment;
		}

		return $uri;
	}

	#[ArrayShape([
		'scheme' => Contract\UrlScheme::class . '|null',
		'username' => 'null|string',
		'password' => 'null|string',
		'host' => 'null|string',
		'port' => 'int|null',
		'authority' => 'null|string',
		'userInfo' => 'null|string',
		'path' => 'string',
		'query' => Contract\QueryMap::class . '|null',
		'fragment' => 'string',
	])]
	#[Pure]
	public function toArray(): array
	{
		return [
			'scheme' => $this->scheme,
			'username' => $this->username,
			'password' => $this->password,
			'host' => $this->host,
			'port' => $this->port,
			'authority' => $this->getAuthority(),
			'userInfo' => $this->getUserInfo(),
			'path' => $this->path,
			'query' => $this->queryMap,
			'fragment' => $this->fragment,
		];
	}

	#[Pure]
	public function with(): Contract\UrlBuilder
	{
		/** @psalm-suppress ImpureMethodCall */
		return new UrlBuilder(
			$this->scheme,
			$this->host,
			$this->port,
			$this->path,
			$this->queryMap === null ? null : new QueryMap($this->queryMap->toArray()),
			$this->fragment,
			$this->username,
			$this->password,
		);
	}

	public function getScheme(): string
	{
		return $this->scheme?->getScheme() ?? '';
	}

	public function getHost(): string
	{
		return $this->host ?? '';
	}

	public function getPort(): ?int
	{
		return $this->port ?? $this->scheme?->getDefaultPort();
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function getQuery(): string
	{
		return (string) $this->queryMap;
	}

	public function getFragment(): string
	{
		return $this->fragment ?? '';
	}

	public function withScheme($scheme): static
	{
		assert(is_string($scheme));

		$urlScheme = UrlScheme::tryFrom(Casing::toLower($scheme)) ?? new CustomUrlScheme($scheme);

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->scheme($urlScheme);
	}

	public function withUserInfo($user, $password = null): static
	{
		assert(is_string($user));
		assert(is_string($password) || $password === null);

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->userInfo($user, $password)->get();
	}

	public function withHost($host): static
	{
		assert(is_string($host));

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->host($host)->get();
	}

	public function withPort($port): static
	{
		assert(is_int($port) || $port === null);

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->port($port)->get();
	}

	public function withPath($path): static
	{
		assert(is_string($path));

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->path($path)->get();
	}

	public function withQuery($query): static
	{
		assert(is_string($query));

		/** @psalm-suppress ImpureMethodCall */
		$map = QueryMap::fromString($query);

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->queryMap($map)->get();
	}

	public function withFragment($fragment): static
	{
		assert(is_string($fragment));

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->fragment($fragment)->get();
	}
}
