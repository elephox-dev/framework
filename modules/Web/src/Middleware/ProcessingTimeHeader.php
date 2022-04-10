<?php
declare(strict_types=1);

namespace Elephox\Web\Middleware;

use Closure;
use Elephox\Http\Contract\Request as RequestContract;
use Elephox\Http\Contract\ResponseBuilder as ResponseBuilderContract;
use Elephox\Http\Contract\ServerRequest as ServerRequestContract;
use Elephox\Http\ParameterSource;
use Elephox\Web\Contract\WebMiddleware;

class ProcessingTimeHeader implements WebMiddleware
{
	public function handle(RequestContract $request, Closure $next): ResponseBuilderContract
	{
		$start = microtime(true);
		$responseBuilder = $next($request);
		$end = microtime(true);

		$diff = round(($end - $start) * 1000, 3);
		$responseBuilder->header('X-Processing-Time', (string)$diff);

		return $responseBuilder;
	}
}
