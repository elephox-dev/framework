<?php
declare(strict_types=1);

namespace Elephox\Host\Middleware;

use Closure;
use Elephox\Host\Contract\WebMiddleware;
use Elephox\Http\Contract\Request as RequestContract;
use Elephox\Http\Contract\ResponseBuilder as ResponseBuilderContract;
use Elephox\Http\ResponseCode;

class FallbackResponseCreator implements WebMiddleware
{
	public function handle(RequestContract $request, Closure $next): ResponseBuilderContract
	{
		$responseBuilder = $next($request);
		if ($responseBuilder->getResponseCode() === null) {
			$responseBuilder->responseCode(ResponseCode::NotFound);
		}

		return $responseBuilder;
	}
}
