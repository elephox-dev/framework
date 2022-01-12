<?php
declare(strict_types=1);

namespace Elephox\Http;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
enum ParameterSource
{
	case Url;
	case Post;
	case Get;
	case Cookie;
	case Session;
	case File;
	case Server;
}
