<?php
declare(strict_types=1);

namespace Elephox\Http;

use JetBrains\PhpStorm\Pure;

enum RequestMethod: string implements Contract\RequestMethod
{
	case GET = "GET";
	case HEAD = "HEAD";
	case POST = "POST";
	case PUT = "PUT";
	case DELETE = "DELETE";
	case OPTIONS = "OPTIONS";
	case PATCH = "PATCH";

	#[Pure] public function canHaveBody(): bool
	{
		return match ($this) {
			self::POST,
			self::PUT,
			self::DELETE,
			self::PATCH => true,
			default => false
		};
	}

	#[Pure] public function getValue(): string
	{
		return $this->value;
	}
}
