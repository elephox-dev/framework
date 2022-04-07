<?php
declare(strict_types=1);

namespace Elephox\Http;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
enum RequestMethod: string
{
	case GET = 'GET';
	case HEAD = 'HEAD';
	case POST = 'POST';
	case PUT = 'PUT';
	case DELETE = 'DELETE';
	case OPTIONS = 'OPTIONS';
	case PATCH = 'PATCH';
	#[Pure]
	public function canHaveBody(): bool
	{
		return match ($this) {
			self::POST,
			self::PUT,
			self::DELETE,
			self::PATCH => true,
			default => false
		};
	}
}
