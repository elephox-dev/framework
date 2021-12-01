<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Attribute\Http;

use Attribute;
use Elephox\Core\Handler\Attribute\RequestHandler;
use Elephox\Core\Handler\UrlTemplate;
use Elephox\Http\RequestMethod;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Put extends RequestHandler
{
	public function __construct(string|UrlTemplate $url, int $weight = 0)
	{
		parent::__construct($url, RequestMethod::PUT, $weight);
	}
}
