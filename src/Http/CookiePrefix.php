<?php

namespace Philly\Http;

use Philly\Http\Contract\Cookie;

enum CookiePrefix: string
{
	case Host = "__Host-";
	case Secure = "__Secure-";

	public function complies(Cookie $cookie): bool
	{
		if (!str_starts_with($cookie->getName(), $this->value)) {
			return false;
		}

		return match ($this) {
			self::Host => $cookie->isSecure() && $cookie->getDomain() === null && $cookie->getPath() === '/',
			self::Secure => $cookie->isSecure(),
		};
	}
}
