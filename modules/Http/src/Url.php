<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\OOR\Casing;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\UriInterface;
use Stringable;

#[Immutable]
class Url implements Stringable, UriInterface
{
	/**
	 * @see https://regex101.com/r/bsN8uj/5
	 */
	public const Pattern = /** @lang RegExp */ '/^(?:(?<scheme>[^:\/?#]+):(?=\D))?(?:(?:\/\/)?(?:(?:(?<user>[^:]*)(?::(?<pass>[^@]*))?@)?(?:(?<=.)(?<host>[^[\/:?#]+|\[[^]]+])(?::(?<port>[^\/:?#]+)?)?|(?<host2>(?&host)):(?<port2>(?&port)))|(?<=\/\/)\/)?)?(?<path>[^?#]*)(?<query>\?[^#]*)?(?<fragment>#.*)?$/i';

	public static function fromString(string|Stringable $url): self
	{
		$builder = new UrlBuilder();

		preg_match(
			self::Pattern,
			(string) $url,
			$matches,
		);
		/**
		 * @var array{scheme: string, user: string, pass: string, host?: string, host2?: string, port?: string, port2?: string, path: string, query: string, fragment: string} $matches
		 */
		if ($matches['scheme'] !== '') {
			$scheme = $matches['scheme'];

			/** @var null|Contract\UrlScheme $urlScheme */
			$urlScheme = UrlScheme::tryFrom(strtolower($scheme));
			$urlScheme ??= new CustomUrlScheme($scheme);

			$builder->scheme($urlScheme);
		}

		$username = $matches['user'] === '' ? null : $matches['user'];
		$password = $matches['pass'] === '' ? null : $matches['pass'];
		$builder->userInfo($username, $password);

		if (isset($matches['host'])) {
			$builder->host($matches['host']);
		} elseif (isset($matches['host2'])) {
			$builder->host($matches['host2']);
		} else {
			$builder->host(null);
		}

		if (isset($matches['port']) && ctype_digit($matches['port'])) {
			$builder->port((int) $matches['port']);
		} elseif (isset($matches['port2']) && ctype_digit($matches['port2'])) {
			$builder->port((int) $matches['port2']);
		} else {
			$builder->port(null);
		}

		$path = $matches['path'];
		if (str_contains($path, ' ')) {
			$path = str_replace(' ', '%20', $path);
		}

		$builder->path($path);

		if (array_key_exists('query', $matches) && strlen($matches['query']) > 1 && str_starts_with($matches['query'], '?')) {
			$builder->queryMap(QueryMap::fromString(substr($matches['query'], 1)));
		}

		if (array_key_exists('fragment', $matches) && strlen($matches['fragment']) > 1 && str_starts_with($matches['fragment'], '#')) {
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
		if ($authority === null) {
			return '';
		}

		$port = $this->port;
		if ($port !== null) {
			$authority .= ":$port";
		}

		$userInfo = $this->getUserInfo();
		if ($userInfo !== '') {
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
		if ($authority !== '') {
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
		'fragment' => 'null|string',
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
		return $this->port === null || $this->port === $this->scheme?->getDefaultPort() ? null
			: $this->port;
	}

	public function getPath(): string
	{
		if (str_starts_with($this->path, '//') && !empty($this->getHost())) {
			return '/' . ltrim($this->path, '/');
		}

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
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($scheme)) {
			throw new InvalidArgumentException("Expected type 'string', got " . get_debug_type($scheme));
		}

		$urlScheme = UrlScheme::tryFrom(Casing::toLower($scheme)) ?? new CustomUrlScheme($scheme);

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->scheme($urlScheme)->get();
	}

	public function withUserInfo($user, $password = null): static
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($user)) {
			throw new InvalidArgumentException("Expected type 'string', got " . get_debug_type($user));
		}

		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($password) && $password !== null) {
			throw new InvalidArgumentException("Expected type 'string' or 'null', got " . get_debug_type($password));
		}

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->userInfo($user, $password)->get();
	}

	public function withHost($host): static
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($host)) {
			throw new InvalidArgumentException("Expected type 'string', got " . get_debug_type($host));
		}

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->host($host)->get();
	}

	public function withPort($port): static
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_int($port) && $port !== null) {
			throw new InvalidArgumentException("Expected type 'int' or 'null', got " . get_debug_type($port));
		}

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->port($port)->get();
	}

	public function withPath($path): static
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($path)) {
			throw new InvalidArgumentException("Expected type 'string', got " . get_debug_type($path));
		}

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->path($path)->get();
	}

	public function withQuery($query): static
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($query)) {
			throw new InvalidArgumentException("Expected type 'string', got " . get_debug_type($query));
		}

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
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($fragment)) {
			throw new InvalidArgumentException("Expected type 'string', got " . get_debug_type($fragment));
		}

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->fragment($fragment)->get();
	}
}
