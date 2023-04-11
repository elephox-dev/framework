<?php
declare(strict_types=1);

namespace Elephox\Http;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
enum RequestMethod: string implements Contract\RequestMethod
{
	case GET = 'GET';
	case POST = 'POST';
	case PUT = 'PUT';
	case PATCH = 'PATCH';
	case DELETE = 'DELETE';
	case HEAD = 'HEAD';
	case OPTIONS = 'OPTIONS';

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

	#[Pure]
	public function getValue(): string
	{
		return $this->value;
	}
}
