<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Attribute\Http;

use Attribute;
use Elephox\Core\Handler\Contract\UrlTemplate;
use Elephox\Http\RequestMethod;
use Elephox\Web\Routing\Attribute\Controller;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Options extends Controller
{
	public function __construct(string $url = '', int $weight = 0)
	{
		parent::__construct($url, $weight, RequestMethod::OPTIONS);
	}
}
