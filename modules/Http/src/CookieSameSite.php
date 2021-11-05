<?php

namespace Philly\Http;

enum CookieSameSite: string
{
	case Strict = "Strict";
	case Lax = "Lax";
	case None = "None";
}
