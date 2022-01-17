<?php
declare(strict_types=1);

namespace Elephox\Http;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
enum ParameterSource
{
	case Post;
	case Get;
	case Session;
	case Server;
	case Env;
}
