<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\Cookie;

enum CookiePrefix: string
{
	case Host = "__Host-";
	case Secure = "__Secure-";

	public function isCompliant(Cookie $cookie): bool
	{
		/**
		 * Suppress until vimeo/psalm#6468 is fixed
		 * @psalm-suppress MixedArgument
		 * @psalm-suppress UndefinedThisPropertyFetch Until vimeo/psalm#6468 is fixed
		 */
		if (!str_starts_with($cookie->getName(), $this->value)) {
			return false;
		}

		return match ($this) {
			self::Host => $cookie->isSecure() && $cookie->getDomain() === null && $cookie->getPath() === '/',
			self::Secure => $cookie->isSecure(),
		};
	}
}
