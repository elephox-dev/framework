<?php
declare(strict_types=1);

namespace Elephox\Web\Middleware;

use Closure;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Http\HeaderName;
use Elephox\Http\ResponseCode;
use Elephox\Mimey\MimeType;
use Elephox\Web\Contract\WebMiddleware;
use Elephox\Web\Routing\NoRouteFoundException;
use JsonException;

class DefaultNotFoundHandler implements WebMiddleware
{
	public function handle(Request $request, Closure $next): ResponseBuilder
	{
		/** @var ResponseBuilder $response */
		$response = $next($request);

		if ($this->shouldHandle($response)) {
			$response->responseCode(ResponseCode::NotFound);

			if ($response->getHeaderMap()?->has(HeaderName::ContentType)) {
				switch ($response->getHeaderMap()->get(HeaderName::ContentType)[0]) {
					case MimeType::TextHtml->value:
						$this->setHtmlNotFound($response);

						break;
					case MimeType::ApplicationJson->value:
						$this->setJsonNotFound($response);

						break;
					default:
						$this->setPlainNotFound($response);

						break;
				}
			} else {
				$this->setHtmlNotFound($response);
			}
		}

		return $response;
	}

	protected function shouldHandle(ResponseBuilder $response): bool
	{
		$exception = $response->getException();

		return $exception instanceof NoRouteFoundException && $response->getBody() === null;
	}

	protected function setHtmlNotFound(ResponseBuilder $response): void
	{
		$response->htmlBody(<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
	<title>404: Not Found</title>
	<style rel="stylesheet">
		html, body {
			margin: 1rem;
			font-family: sans-serif;
		}

		@media (prefers-color-scheme: dark) {
			body {
				background: #202124;
				color: #fff;
			}
		}
	</style>
</head>
<body>
	<h1>404: Not Found</h1>
	<p>
		The resource you requested is not available.
	</p>
</body>
</html>
HTML);
	}

	protected function setPlainNotFound(ResponseBuilder $response): void
	{
		$response->textBody('Error 404: The resource you requested is not available.');
	}

	protected function setJsonNotFound(ResponseBuilder $response): void
	{
		try {
			// MAYBE: use a standardized schema for errors in json
			$response->jsonBody([
				'status' => 404,
				'error' => 'not-found',
				'message' => 'The resource you requested is not available',
			]);
		} catch (JsonException) {
			$this->setPlainNotFound($response);
		}
	}
}
