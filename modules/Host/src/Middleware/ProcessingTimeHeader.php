<?php
declare(strict_types=1);

namespace Elephox\Host\Middleware;

use Closure;
use Elephox\Host\Contract\WebMiddleware;
use Elephox\Http\Contract\Request as RequestContract;
use Elephox\Http\Contract\ResponseBuilder as ResponseBuilderContract;
use Elephox\Http\Contract\ServerRequest as ServerRequestContract;
use Elephox\Http\ParameterSource;

class ProcessingTimeHeader implements WebMiddleware
{
	public function handle(RequestContract $request, Closure $next): ResponseBuilderContract
	{
		$responseBuilder = $next($request);
		if ($request instanceof ServerRequestContract) {
			$requestStart = (float)$request->getParameters()->get('REQUEST_TIME_FLOAT', ParameterSource::Server);
			$now = microtime(true);
			$diff = round($now - $requestStart, 5);
			$responseBuilder->header('X-Processing-Time', [(string)$diff]);
		}

		return $responseBuilder;
	}
}
