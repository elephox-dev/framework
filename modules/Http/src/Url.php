<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Support\StringableProxy;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class Url implements Contract\Url
{
	use StringableProxy;

	public const Pattern = '/^(?<scheme>[^:]*:\/\/|\/\/)?(?:(?:(?<username>[^:@]+)(?::(?<password>[^@]+))?@)?(?<host>[^:\/\?#]+)(?::(?<port>\d+))?)?(?<path>[^\?#]*)(?<query>\?[^#]*)?(?<fragment>#.*)?$/';

	public static function fromString(string $uri): Contract\Url
	{
		return new Url($uri);
	}

	private ?string $scheme;
	private ?string $username;
	private ?string $password;
	private ?string $host;
	private ?int $port;
	private string $path;
	private ?string $query;
	private ?string $fragment;

	final private function __construct(string $source)
	{
		preg_match(
			self::Pattern,
			$source,
			$matches
		);

		if (str_ends_with($matches['scheme'], '://')) {
			$this->scheme = substr($matches['scheme'], 0, -3);
		} else if ($matches['scheme'] === '//') {
			$this->scheme = '';
		} else {
			$this->scheme = null;
		}

		$this->username = empty($matches['username']) ? null : $matches['username'];
		$this->password = empty($matches['password']) ? null : $matches['password'];
		$this->host = empty($matches['host']) ? null : $matches['host'];
		$this->port = ctype_digit($matches['port']) ? (int)$matches['port'] : null;
		$this->path = $matches['path'];

		if (array_key_exists('query', $matches) && str_starts_with($matches['query'], '?')) {
			if ($matches['query'] === '?') {
				$this->query = '';
			} else {
				$this->query = substr($matches['query'], 1);
			}
		} else {
			$this->query = null;
		}

		if (array_key_exists('fragment', $matches) && str_starts_with($matches['fragment'], '#')) {
			if ($matches['fragment'] === '#') {
				$this->fragment = '';
			} else {
				$this->fragment = substr($matches['fragment'], 1);
			}
		} else {
			$this->fragment = null;
		}
	}

	#[Pure] public function getScheme(): ?string
	{
		return $this->scheme;
	}

	public function getUrlScheme(): ?UrlScheme
	{
		if ($this->scheme === null) {
			return null;
		}

		/**
		 * @var UrlScheme|null
		 * @psalm-suppress UndefinedMethod Until vimeo/psalm#6429 is fixed.
		 */
		return UrlScheme::tryFrom($this->scheme);
	}

	#[Pure] public function getUsername(): ?string
	{
		return $this->username;
	}

	#[Pure] public function getPassword(): ?string
	{
		return $this->password;
	}

	#[Pure] public function getHost(): ?string
	{
		return $this->host;
	}

	#[Pure] public function getPort(): ?int
	{
		return $this->port;
	}

	public function getPath(): string
	{
		$scheme = $this->getUrlScheme();
		if ($scheme === null || !$scheme->usesTrimmedPath()) {
			return $this->path;
		}

		return trim($this->path, '/');
	}

	#[Pure] public function getQuery(): ?string
	{
		return $this->query;
	}

	#[Pure] public function getFragment(): ?string
	{
		return $this->fragment;
	}

	#[Pure] public function toString(): string
	{
		if ($this->scheme !== null) {
			if (empty($this->scheme)) {
				$uri = '//';
			} else {
				$uri = $this->scheme . '://';
			}
		} else {
			$uri = '';
		}

		if ($this->username !== null || $this->password !== null) {
			if ($this->username !== null) {
				$uri .= $this->username;

				if ($this->password !== null) {
					$uri .= ':' . $this->password;
				}
			}

			$uri .= '@';
		}

		if ($this->host !== null) {
			$uri .= $this->host;

			if ($this->port !== null) {
				$uri .= ':' . $this->port;
			}
		}

		$uri .= $this->path;

		if ($this->query !== null) {
			$uri .= '?' . $this->query;
		}

		if ($this->fragment !== null) {
			$uri .= '#' . $this->fragment;
		}

		return $uri;
	}

	#[ArrayShape([
		'scheme' => "null|string",
		'username' => "null|string",
		'password' => "null|string",
		'host' => "null|string",
		'port' => "int|null",
		'path' => "string",
		'query' => "null|string",
		'fragment' => "null|string"
	])]
	public function asArray(): array
	{
		return [
			'scheme' => $this->getScheme(),
			'username' => $this->getUsername(),
			'password' => $this->getPassword(),
			'host' => $this->getHost(),
			'port' => $this->getPort(),
			'path' => $this->getPath(),
			'query' => $this->getQuery(),
			'fragment' => $this->getFragment(),
		];
	}
}
