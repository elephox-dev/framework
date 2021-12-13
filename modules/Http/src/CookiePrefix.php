<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\Cookie;
use JetBrains\PhpStorm\Immutable;

#[Immutable]
enum CookiePrefix: string
{
	case Host = "__Host-";
	case Secure = "__Secure-";

	public function isCompliant(Cookie $cookie): bool
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
