<?php

namespace Elephox\Web\Middleware;

use Closure;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Mimey\MimeType;
use Elephox\Stream\StringStream;
use Elephox\Web\Contract\WebMiddleware;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\XmlResponseHandler;
use Whoops\RunInterface as WhoopsRunInterface;

class WhoopsExceptionHandler implements WebMiddleware
{
	/**
	 * @param Closure(): WhoopsRunInterface $whoosRunInterfaceFactory
	 */
	public function __construct(
		private $whoosRunInterfaceFactory,
	)
	{
	}

	public function handle(Request $request, Closure $next): ResponseBuilder
	{
		$response = $next($request);

		if ($exception = $response->getException()) {
			$runner = ($this->whoosRunInterfaceFactory)();

			if (empty($runner->getHandlers())) {
				if ($contentType = $response->getContentType()) {
					$runner->pushHandler(match ($contentType->getValue()) {
						MimeType::ApplicationJson->getValue() => new JsonResponseHandler(),
						MimeType::ApplicationXml->getValue() => new XmlResponseHandler(),
						MimeType::TextPlain->getValue() => new PlainTextHandler(),
						default => new PrettyPageHandler(),
					});
				} else {
					$runner->pushHandler(new PrettyPageHandler());
					$response->contentType(MimeType::TextHtml);
				}
			}

			$runner->allowQuit(false);
			$runner->writeToOutput(false);

			/** @var non-empty-string $content */
			$content = $runner->handleException($exception);
			$response->body(new StringStream($content));
		}

		return $response;
	}
}
