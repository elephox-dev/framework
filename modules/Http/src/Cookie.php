<?php
declare(strict_types=1);

namespace Elephox\Http;

use DateTime;
use InvalidArgumentException;
use Elephox\Collection\ArrayList;
use Elephox\Collection\ArrayMap;
use Elephox\Collection\KeyValuePair;

class Cookie implements Contract\Cookie
{
	public const ExpiresFormat = "D, d-M-Y H:i:s T";

	/**
	 * @param string $cookies
	 * @return ArrayList<Contract\Cookie>
	 */
	public static function fromRequestString(string $cookies): ArrayList
	{
		return ArrayList::fromArray(mb_split(';', $cookies))
			->map(static function (string $cookie): Contract\Cookie {
				[$name, $value] = explode('=', $cookie, 2);

				/** @var Contract\Cookie */
				return new self(trim($name), $value);
			});
	}

	/**
	 * @param string $cookieString
	 * @return \Elephox\Http\Contract\Cookie
	 */
	public static function fromResponseString(string $cookieString): Contract\Cookie
	{
		/** @var array<string> $split */
		$split = mb_split(';', $cookieString);
		$propertyList = ArrayList::fromArray($split);
		$nameValuePair = $propertyList->shift();
		[$name, $value] = explode('=', $nameValuePair, 2);

		/** @var ArrayList<KeyValuePair<string, string>> $propertyList */
		$propertyList = $propertyList
			->map(static function (string $keyValue): KeyValuePair {
				if (!str_contains($keyValue, '=')) {
					return new KeyValuePair(strtolower(trim($keyValue)), "");
				}

				[$key, $value] = explode('=', $keyValue, 2);

				return new KeyValuePair(strtolower(trim($key)), $value);
			});

		/**
		 * @psalm-suppress InvalidArgument The generic types are subtypes of the expected ones.
		 */
		$propertyMap = ArrayMap::fromKeyValuePairList($propertyList);

		$cookie = new self($name);

		if ($value !== '') {
			$cookie->setValue($value);
		}

		if ($propertyMap->has('expires')) {
			$cookie->setExpires(DateTime::createFromFormat(self::ExpiresFormat, $propertyMap->get('expires')));
		}

		if ($propertyMap->has('path')) {
			$cookie->setPath($propertyMap->get('path'));
		}

		if ($propertyMap->has('domain')) {
			$cookie->setDomain($propertyMap->get('domain'));
		}

		if ($propertyMap->has('secure')) {
			$cookie->setSecure(true);
		}

		if ($propertyMap->has('httponly')) {
			$cookie->setHttpOnly(true);
		}

		if ($propertyMap->has('samesite')) {
			$sameSite = CookieSameSite::from($propertyMap->get('samesite'));

			$cookie->setSameSite($sameSite);
		}

		if ($propertyMap->has('max-age')) {
			$maxAge = $propertyMap->get('max-age');
			if (!ctype_digit($maxAge)) {
				throw new InvalidArgumentException("The max-age property must be an integer.");
			}

			$cookie->setMaxAge((int)$maxAge);
		}

		return $cookie;
	}

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
	)
	{
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getValue(): ?string
	{
		return $this->value;
	}

	public function setValue(?string $value): void
	{
		$this->value = $value;
	}

	public function getExpires(): ?DateTime
	{
		return $this->expires;
	}

	public function setExpires(?DateTime $expires): void
	{
		$this->expires = $expires;
	}

	public function getPath(): ?string
	{
		return $this->path;
	}

	public function setPath(?string $path): void
	{
		$this->path = $path;
	}

	public function getDomain(): ?string
	{
		return $this->domain;
	}

	public function setDomain(?string $domain): void
	{
		$this->domain = $domain;
	}

	public function isSecure(): bool
	{
		return $this->secure;
	}

	public function setSecure(bool $secure): void
	{
		$this->secure = $secure;
	}

	public function isHttpOnly(): bool
	{
		return $this->httpOnly;
	}

	public function setHttpOnly(bool $httpOnly): void
	{
		$this->httpOnly = $httpOnly;
	}

	public function getSameSite(): ?CookieSameSite
	{
		return $this->sameSite;
	}

	public function setSameSite(?CookieSameSite $sameSite): void
	{
		$this->sameSite = $sameSite;
	}

	public function getMaxAge(): ?int
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
}
