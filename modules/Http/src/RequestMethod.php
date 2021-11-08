<?php
declare(strict_types=1);

namespace Philly\Http;

enum RequestMethod: string
{
	case GET = "GET";
	case HEAD = "HEAD";
	case POST = "POST";
	case PUT = "PUT";
	case DELETE = "DELETE";
	case OPTIONS = "OPTIONS";
	case PATCH = "PATCH";
}
