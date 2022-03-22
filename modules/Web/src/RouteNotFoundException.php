<?php
declare(strict_types=1);

namespace Elephox\Web;

use Elephox\Http\Contract\Request;
use RuntimeException;
use Throwable;

class RouteNotFoundException extends RuntimeException
{
	public function __construct(public readonly Request $request, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("No handler found for route '{$this->request->getUrl()}'", $code, $previous);
	}
}
