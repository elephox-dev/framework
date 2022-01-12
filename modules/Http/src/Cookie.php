<?php
declare(strict_types=1);

namespace Elephox\Http;

use DateTime;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;

class Cookie implements Contract\Cookie
{
	public const ExpiresFormat = "D, d-M-Y H:i:s T";

	public function __construct(
		private string          $name,
		private ?string         $value = null,
		private ?DateTime       $expires = null,
		private ?string         $path = null,
		private ?string         $domain = null,
		private bool            $secure = false,
		private bool            $httpOnly = false,
		private ?CookieSameSite $sameSite = null,
		public ?int             $maxAge = null
	) {
	}

	#[Pure] public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	#[Pure] public function getValue(): ?string
	{
		return $this->value;
	}

	public function setValue(?string $value): void
	{
		$this->value = $value;
	}

	#[Pure] public function getExpires(): ?DateTime
	{
		return $this->expires;
	}

	public function setExpires(?DateTime $expires): void
	{
		$this->expires = $expires;
	}

	#[Pure] public function getPath(): ?string
	{
		return $this->path;
	}

	public function setPath(?string $path): void
	{
		$this->path = $path;
	}

	#[Pure] public function getDomain(): ?string
	{
		return $this->domain;
	}

	public function setDomain(?string $domain): void
	{
		$this->domain = $domain;
	}

	#[Pure] public function isSecure(): bool
	{
		return $this->secure;
	}

	public function setSecure(bool $secure): void
	{
		$this->secure = $secure;
	}

	#[Pure] public function isHttpOnly(): bool
	{
		return $this->httpOnly;
	}

	public function setHttpOnly(bool $httpOnly): void
	{
		$this->httpOnly = $httpOnly;
	}

	#[Pure] public function getSameSite(): ?CookieSameSite
	{
		return $this->sameSite;
	}

	public function setSameSite(?CookieSameSite $sameSite): void
	{
		$this->sameSite = $sameSite;
	}

	#[Pure] public function getMaxAge(): ?int
	{
		return $this->maxAge;
	}

	public function setMaxAge(?int $maxAge): void
	{
		$this->maxAge = $maxAge;
	}

	public function __toString(): string
	{
		$cookie = $this->name . '=' . ($this->value ?? '');

		if ($this->expires) {
			$cookie .= '; Expires=' . $this->expires->format(self::ExpiresFormat);
		}

		if ($this->path) {
			$cookie .= '; Path=' . $this->path;
		}

		if ($this->domain) {
			$cookie .= '; Domain=' . $this->domain;
		}

		if ($this->secure) {
			$cookie .= '; Secure';
		}

		if ($this->httpOnly) {
			$cookie .= '; HttpOnly';
		}

		if ($this->sameSite) {
			$sameSite = $this->sameSite->value;

			$cookie .= '; SameSite=' . $sameSite;
		}

		if ($this->maxAge) {
			$cookie .= '; Max-Age=' . $this->maxAge;
		}

		return $cookie;
	}

	#[ArrayShape([
		'name' => "string",
		'value' => "null|string",
		'expires' => "\DateTime|null",
		'path' => "null|string",
		'domain' => "null|string",
		'secure' => "bool",
		'httpOnly' => "bool",
		'sameSite' => "\Elephox\Http\CookieSameSite|null"
	])]
	public function toArray(): array
	{
		return [
			'name' => $this->name,
			'value' => $this->value,
			'expires' => $this->expires,
			'path' => $this->path,
			'domain' => $this->domain,
			'secure' => $this->secure,
			'httpOnly' => $this->httpOnly,
			'sameSite' => $this->sameSite,
		];
	}

	public function offsetExists(
		#[ExpectedValues(['name', 'value', 'expires', 'path', 'domain', 'secure', 'httpOnly', 'sameSite'])]
		mixed $offset
	): bool
	{
		return match ($offset) {
			'name', 'value', 'secure', 'httpOnly' => true,
			'expires' => $this->expires !== null,
			'path' => $this->path !== null,
			'domain' => $this->domain !== null,
			'sameSite' => $this->sameSite !== null,
			default => false,
		};
	}

	public function offsetGet(
		#[ExpectedValues(['name', 'value', 'expires', 'path', 'domain', 'secure', 'httpOnly', 'sameSite'])]
		mixed $offset
	): string|bool|null|DateTime|CookieSameSite
	{
		return match ($offset) {
			'name' => $this->name,
			'value' => $this->value,
			'expires' => $this->expires ?? throw new InvalidArgumentException("Cookie 'expires' is not set"),
			'path' => $this->path ?? throw new InvalidArgumentException("Cookie 'path' is not set"),
			'domain' => $this->domain ?? throw new InvalidArgumentException("Cookie 'domain' is not set"),
			'secure' => $this->secure,
			'httpOnly' => $this->httpOnly,
			'sameSite' => $this->sameSite ?? throw new InvalidArgumentException("Cookie 'sameSite' is not set"),
			default => throw new InvalidArgumentException("Cookie '$offset' is not set")
		};
	}

	public function offsetSet(
		#[ExpectedValues(['name', 'value', 'expires', 'path', 'domain', 'secure', 'httpOnly', 'sameSite'])]
		mixed $offset,
		mixed $value
	): void
	{
		if (!is_string($offset)) {
			throw new InvalidArgumentException("Cookie '$offset' is not a valid property name");
		}

		$method = 'set' . ucfirst($offset);
		if (!method_exists($this, $method)) {
			throw new InvalidArgumentException("Cookie '$offset' cannot be set");
		}

		$this->{$method}($value);
	}

	public function offsetUnset(
		#[ExpectedValues(['name', 'value', 'expires', 'path', 'domain', 'secure', 'httpOnly', 'sameSite'])]
		mixed $offset
	): void
	{
		if (!is_string($offset)) {
			throw new InvalidArgumentException("Cookie '$offset' is not a valid property name");
		}

		$method = 'set' . ucfirst($offset);
		if ($offset === 'name' ||!method_exists($this, $method)) {
			throw new InvalidArgumentException("Cookie '$offset' cannot be unset");
		}

		if ($offset === 'secure' || $offset === 'httpOnly') {
			$this->{$method}(false);
		} else {
			$this->{$method}(null);
		}
	}
}
