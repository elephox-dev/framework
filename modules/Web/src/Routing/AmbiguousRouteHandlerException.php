<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Elephox\Http\Contract\Request;
use RuntimeException;
use Stringable;
use Throwable;

class AmbiguousRouteHandlerException extends RuntimeException
{
	/**
	 * @param Request $request
	 * @param list<Stringable> $routes
	 */
	public function __construct(public readonly Request $request, public readonly array $routes, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Multiple handlers matched route '{$this->request->getUrl()}': " . implode(', ', $routes), $code, $previous);
	}
}
