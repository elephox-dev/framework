<?php
declare(strict_types=1);

namespace Elephox\Http;

enum CookieSameSite: string
{
	case Strict = "Strict";
	case Lax = "Lax";
	case None = "None";
}
