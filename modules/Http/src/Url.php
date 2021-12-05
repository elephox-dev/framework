<?php
declare(strict_types=1);

namespace Elephox\Http;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class Url implements Contract\Url
{
	public const Pattern = /** @lang RegExp */ '/^(?<scheme>[^:]*:\/\/|\/\/)?(?:(?:(?<username>[^:@]+)(?::(?<password>[^@]+))?@)?(?<host>[^:\/?#]+)(?::(?<port>\d+))?)?(?<path>[^?#]*)(?<query>\?[^#]*)?(?<fragment>#.*)?$/';

	public static function fromString(string $uri): Contract\Url
	{
		preg_match(
			self::Pattern,
			$uri,
			$matches
		);

		if (str_ends_with($matches['scheme'], '://')) {
			$scheme = substr($matches['scheme'], 0, -3);
		} else if ($matches['scheme'] === '//') {
			$scheme = '';
		} else {
			$scheme = null;
		}

		$username = empty($matches['username']) ? null : $matches['username'];
		$password = empty($matches['password']) ? null : $matches['password'];
		$host = empty($matches['host']) ? null : $matches['host'];
		$port = ctype_digit($matches['port']) ? (int)$matches['port'] : null;
		$path = $matches['path'];

		if (array_key_exists('query', $matches) && str_starts_with($matches['query'], '?')) {
			if ($matches['query'] === '?') {
				$query = '';
			} else {
				$query = substr($matches['query'], 1);
			}
		} else {
			$query = null;
		}

		if (array_key_exists('fragment', $matches) && str_starts_with($matches['fragment'], '#')) {
			if ($matches['fragment'] === '#') {
				$fragment = '';
			} else {
				$fragment = substr($matches['fragment'], 1);
			}
		} else {
			$fragment = null;
		}

		return new self($scheme, $username, $password, $host, $port, $path, $query, $fragment);
	}

	#[Pure] final private function __construct(
		private ?string $scheme,
		private ?string $username,
		private ?string $password,
		private ?string $host,
		private ?int    $port,
		private string  $path,
		private ?string $query,
		private ?string $fragment,
	)
	{}

	#[Pure] public function getScheme(): string
	{
		return $this->scheme ?? "";
	}

	public function getUrlScheme(): ?UrlScheme
	{
		if ($this->scheme === null) {
			return null;
		}

		return UrlScheme::tryFrom($this->scheme);
	}

	#[Pure] public function getAuthority(): string
	{
		if ($this->host === null) {
			return "";
		}

		$authority = $this->host;

		if ($this->port !== null) {
			$authority .= ":$this->port";
		}

		if ($this->username !== null) {
			$userInfo = $this->username;

			if ($this->password !== null) {
				$userInfo .= ":$this->password";
			}

			$authority = "$userInfo@$authority";
		}

		return $authority;
	}

	#[Pure] public function getUsername(): ?string
	{
		return $this->username;
	}

	#[Pure] public function getPassword(): ?string
	{
		return $this->password;
	}

	#[Pure] public function getUserInfo(): string
	{
		if ($this->username === null) {
			return "";
		}

		$userInfo = $this->username;

		if ($this->password !== null) {
			$userInfo .= ":$this->password";
		}

		return $userInfo;
	}

	#[Pure] public function getHost(): string
	{
		return $this->host ?? "";
	}

	public function getPort(): ?int
	{
		$default = $this->getUrlScheme()?->getDefaultPort();

		return $default === $this->port ? null : $this->port;
	}

	#[Pure] public function getPath(): string
	{
		return $this->path;
	}

	#[Pure] public function getQuery(): string
	{
		return $this->query ?? "";
	}

	#[Pure] public function getFragment(): string
	{
		return $this->fragment ?? "";
	}

	#[Pure] public function __toString(): string
	{
		if ($this->scheme !== null) {
			$uri = $this->scheme . ':';
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

		if ($this->query !== null) {
			$uri .= '?' . $this->query;
		}

		if ($this->fragment !== null) {
			$uri .= '#' . $this->fragment;
		}

		return $uri;
	}

	#[ArrayShape([
		'scheme' => "string",
		'username' => "null|string",
		'password' => "null|string",
		'host' => "string",
		'port' => "int|null",
		'authority' => "string",
		'userInfo' => "string",
		'path' => "string",
		'query' => "string",
		'fragment' => "string"
	])]
	public function asArray(): array
	{
		return [
			'scheme' => $this->getScheme(),
			'username' => $this->getUsername(),
			'password' => $this->getPassword(),
			'host' => $this->getHost(),
			'port' => $this->getPort(),
			'authority' => $this->getAuthority(),
			'userInfo' => $this->getUserInfo(),
			'path' => $this->getPath(),
			'query' => $this->getQuery(),
			'fragment' => $this->getFragment(),
		];
	}

	#[Pure] public function withScheme($scheme): static
	{
		return new static($scheme, $this->username, $this->password, $this->host, $this->port, $this->path, $this->query, $this->fragment);
	}

	#[Pure] public function withUserInfo($user, $password = null): static
	{
		return new static($this->scheme, $user, $password, $this->host, $this->port, $this->path, $this->query, $this->fragment);
	}

	#[Pure] public function withHost($host): static
	{
		return new static($this->scheme, $this->username, $this->password, $host, $this->port, $this->path, $this->query, $this->fragment);
	}

	#[Pure] public function withPort($port): static
	{
		return new static($this->scheme, $this->username, $this->password, $this->host, $port, $this->path, $this->query, $this->fragment);
	}

	#[Pure] public function withPath($path): static
	{
		return new static($this->scheme, $this->username, $this->password, $this->host, $this->port, $path, $this->query, $this->fragment);
	}

	#[Pure] public function withQuery($query): static
	{
		return new static($this->scheme, $this->username, $this->password, $this->host, $this->port, $this->path, $query, $this->fragment);
	}

	#[Pure] public function withFragment($fragment): static
	{
		return new static($this->scheme, $this->username, $this->password, $this->host, $this->port, $this->path, $this->query, $fragment);
	}

	#[Pure] public function withUrlScheme(UrlScheme $scheme): static
	{
		return new static($scheme->value, $this->username, $this->password, $this->host, $this->port, $this->path, $this->query, $this->fragment);
	}
}
