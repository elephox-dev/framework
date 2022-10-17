<?php
declare(strict_types=1);

namespace Elephox\Web\Middleware;

use Closure;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Http\Response;
use Elephox\Http\ResponseCode;
use Elephox\Web\Contract\WebEnvironment;
use Elephox\Web\Contract\WebMiddleware;

class StaticContentHandler implements WebMiddleware
{
	public function __construct(
		protected readonly WebEnvironment $environment,
	) {
	}

	public function handle(Request $request, Closure $next): ResponseBuilder
	{
		$file = $this->environment->getWebRoot()->file($request->getUrl()->path);
		if (!$file->exists()) {
			return $next($request);
		}

		return Response::build()
			->responseCode(ResponseCode::OK)
			->body($file->stream())
		;
	}
}
