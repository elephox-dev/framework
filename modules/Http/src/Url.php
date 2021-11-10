<?php
declare(strict_types=1);

namespace Philly\Http;

use Philly\Support\ToStringCompatible;

class Url implements Contract\Url
{
	use ToStringCompatible;

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

	public function getScheme(): ?string
	{
		return $this->scheme;
	}

	public function getUsername(): ?string
	{
		return $this->username;
	}

	public function getPassword(): ?string
	{
		return $this->password;
	}

	public function getHost(): ?string
	{
		return $this->host;
	}

	public function getPort(): ?int
	{
		return $this->port;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function getQuery(): ?string
	{
		return $this->query;
	}

	public function getFragment(): ?string
	{
		return $this->fragment;
	}

	public function asString(): string
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
}
