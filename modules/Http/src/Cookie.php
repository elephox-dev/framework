<?php

namespace Philly\Http;

use DateTime;
use InvalidArgumentException;
use Philly\Collection\ArrayList;
use Philly\Collection\ArrayMap;
use Philly\Collection\KeyValuePair;
use Philly\Support\ToStringCompatible;

class Cookie implements Contract\Cookie
{
	use ToStringCompatible;

	public const ExpiresFormat = "D, d-M-Y H:i:s T";

	/**
	 * @param string $cookies
	 * @return ArrayList<\Philly\Http\Contract\Cookie>
	 */
	public static function fromRequestString(string $cookies): ArrayList
	{
		return ArrayList::fromArray(mb_split(';', $cookies))
			->map(static function (mixed $cookie): Contract\Cookie {
				/** @var string $cookie */

				[$name, $value] = explode('=', trim($cookie), 2);

				/** @var Contract\Cookie */
				return new self($name, $value);
			});
	}

	/**
	 * @param string $cookieString
	 * @return \Philly\Http\Contract\Cookie
	 */
	public static function fromResponseString(string $cookieString): Contract\Cookie
	{
		$split = mb_split(';', $cookieString);
		if (!$split) {
			throw new InvalidArgumentException("Unable to split cookie.");
		}

		/** @var ArrayList<string> $propertyList */
		$propertyList = ArrayList::fromArray($split);
		$nameValuePair = $propertyList->shift();
		[$name, $value] = explode('=', trim($nameValuePair), 2);

		/** @var ArrayList<\Philly\Collection\Contract\KeyValuePair<string, string>> $propertyList */
		$propertyList = $propertyList
			->map(static function (string $keyValue): KeyValuePair {
				$keyValue = trim($keyValue);

				if (mb_strpos($keyValue, '=') === false) {
					return new KeyValuePair(mb_strtolower($keyValue), "");
				}

				[$key, $value] = explode('=', $keyValue, 2);

				return new KeyValuePair(mb_strtolower($key), $value);
			});

		/** @psalm-suppress InvalidArgument The generic types are subtypes of the expected ones. */
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
			/**
			 * @var \Philly\Http\CookieSameSite $sameSite
			 * @psalm-suppress UndefinedMethod Until vimeo/psalm#6429 is fixed.
			 */
			$sameSite = CookieSameSite::from($propertyMap->get('samesite'));

			$cookie->setSameSite($sameSite);
		}

		if ($propertyMap->has('max-age')) {
			$cookie->setMaxAge((int)$propertyMap->get('max-age'));
		}

		return $cookie;
	}

	private string $name;

	private ?string $value;

	private ?DateTime $expires;

	private ?string $path;

	private ?string $domain;

	private bool $secure;

	private bool $httpOnly;

	private ?CookieSameSite $sameSite;

	public ?int $maxAge;

	public function __construct(
		string          $name,
		?string         $value = null,
		?DateTime       $expires = null,
		?string         $path = null,
		?string         $domain = null,
		bool            $secure = false,
		bool            $httpOnly = false,
		?CookieSameSite $sameSite = null,
		?int            $maxAge = null
	)
	{
		$this->name = $name;
		$this->value = $value;
		$this->expires = $expires;
		$this->path = $path;
		$this->domain = $domain;
		$this->secure = $secure;
		$this->httpOnly = $httpOnly;
		$this->sameSite = $sameSite;
		$this->maxAge = $maxAge;
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

	public function asString(): string
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
			/**
			 * @var string $sameSite
			 * @psalm-suppress UndefinedPropertyFetch Until vimeo/psalm#6468 is fixed
			 */
			$sameSite = $this->sameSite->value;

			$cookie .= '; SameSite=' . $sameSite;
		}

		if ($this->maxAge) {
			$cookie .= '; Max-Age=' . $this->maxAge;
		}

		return $cookie;
	}
}
