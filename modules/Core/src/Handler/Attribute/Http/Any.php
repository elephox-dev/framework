<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Attribute\Http;

use Attribute;
use Elephox\Core\Handler\Attribute\RequestHandler;
use Elephox\Core\Handler\UrlTemplate;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Any extends RequestHandler
{
	public function __construct(string|UrlTemplate $url, int $weight = 0)
	{
		parent::__construct($url, weight: $weight);
	}
}
