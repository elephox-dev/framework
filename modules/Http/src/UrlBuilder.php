<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\OOR\Casing;

class UrlBuilder extends AbstractBuilder implements Contract\UrlBuilder
{
	public function __construct(
		private ?Contract\UrlScheme $scheme = null,
		private ?string $host = null,
		private ?int $port = null,
		private ?string $path = null,
		private ?Contract\QueryMap $queryMap = null,
		private ?string $fragment = null,
		private ?string $username = null,
		private ?string $password = null,
	) {
	}

	public function scheme(?Contract\UrlScheme $scheme): Contract\UrlBuilder
	{
		$this->scheme = $scheme;

		return $this;
	}

	public function host(?string $host): Contract\UrlBuilder
	{
		$this->host = $host !== null ? Casing::toLower($host) : null;

		return $this;
	}

	public function port(?int $port): Contract\UrlBuilder
	{
		$this->port = $port;

		return $this;
	}

	public function path(?string $path): Contract\UrlBuilder
	{
		$this->path = $path;

		return $this;
	}

	public function queryMap(?Contract\QueryMap $query): Contract\UrlBuilder
	{
		$this->queryMap = $query;

		return $this;
	}

	public function fragment(?string $fragment): Contract\UrlBuilder
	{
		$this->fragment = $fragment;

		return $this;
	}

	public function userInfo(?string $username, ?string $password = null): Contract\UrlBuilder
	{
		$this->username = $username;
		$this->password = $password;

		return $this;
	}

	public function get(bool $replaceDefaultPort = true): Url
	{
		if ($replaceDefaultPort && $this->port !== null && $this->scheme !== null && $this->port === $this->scheme->getDefaultPort()) {
			$this->port = null;
		}

		return new Url(
			$this->scheme,
			$this->host,
			$this->port,
			$this->path ?? throw self::missingParameterException('path'),
			$this->queryMap,
			$this->fragment,
			$this->username,
			$this->password,
		);
	}
}
