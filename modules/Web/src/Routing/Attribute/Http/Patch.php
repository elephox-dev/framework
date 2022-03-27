<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Attribute\Http;

use Attribute;
use Elephox\Core\Handler\Contract\UrlTemplate;
use Elephox\Http\RequestMethod;
use Elephox\Web\Routing\Attribute\Contract\RouteAttribute;
use Elephox\Web\Routing\Attribute\Controller;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Patch extends Controller implements RouteAttribute
{
	public function __construct(?string $path = self::DEFAULT_PATH, int $weight = self::DEFAULT_WEIGHT)
	{
		parent::__construct($path, $weight, RequestMethod::PATCH);
	}
}
