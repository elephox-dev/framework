<?php
declare(strict_types=1);

namespace Elephox\Http;

enum RequestMethod: string implements Contract\RequestMethod
{
	case GET = "GET";
	case HEAD = "HEAD";
	case POST = "POST";
	case PUT = "PUT";
	case DELETE = "DELETE";
	case OPTIONS = "OPTIONS";
	case PATCH = "PATCH";

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

	public function getValue(): string
	{
		/**
		 * @var non-empty-string value
		 * @psalm-suppress UndefinedThisPropertyFetch Until vimeo/psalm#6468 is fixed
		 */
		return $this->value;
	}
}
