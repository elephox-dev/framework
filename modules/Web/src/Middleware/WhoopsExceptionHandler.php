<?php

namespace Elephox\Web\Middleware;

use Closure;
use Elephox\DI\Contract\ServiceCollection;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Http\ResponseCode;
use Elephox\Mimey\MimeType;
use Elephox\Stream\StringStream;
use Elephox\Web\Contract\WebMiddleware;
use Whoops\Handler\PrettyPageHandler;
use Whoops\RunInterface as WhoopsRunInterface;

class WhoopsExceptionHandler implements WebMiddleware
{
	public function __construct(
		private readonly ServiceCollection $services
	)
	{
	}

	public function handle(Request $request, Closure $next): ResponseBuilder
	{
		$response = $next($request);

		if ($exception = $response->getException()) {
			$runner = $this->services->requireService(WhoopsRunInterface::class);

			if (empty($runner->getHandlers())) {
				$runner->pushHandler(new PrettyPageHandler());
			}

			/** @var non-empty-string $content */
			$content = $runner->handleException($exception);

			$response
				->body(new StringStream($content))
				->responseCode(ResponseCode::InternalServerError)
				->contentType(MimeType::TextHtml)
			;
		}

		return $response;
	}
}
