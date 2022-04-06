<?php
declare(strict_types=1);

namespace Elephox\Web\Middleware;

use Closure;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Http\Response;
use Elephox\Http\ResponseSender;
use Elephox\Stream\StringStream;
use Elephox\Support\Contract\ExceptionHandler;
use Elephox\Web\Contract\WebMiddleware;
use Throwable;

class DefaultExceptionHandler implements WebMiddleware, ExceptionHandler
{
	public function handle(Request $request, Closure $next): ResponseBuilder
	{
		$response = $next($request);

		if ($response->getException()) {
			$this->setResponseBody($response);
		}

		return $response;
	}

	public function handleException(Throwable $exception): void
	{
		$response = Response::build()->exception($exception);
		$this->setResponseBody($response);
		ResponseSender::sendResponse($response);
	}

	protected function setResponseBody(ResponseBuilder $response): ResponseBuilder
	{
		$exception = $response->getException();
		if ($exception === null) {
			if ($response->getBody() === null) {
				return $response->htmlBody('No exception to handle found');
			}

			return $response;
		}

		return $response->htmlBody(<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Error</title>
	<style rel="stylesheet">
		html, body {
			margin: 1rem;
			text-align: center;
			font-family: sans-serif;
		}

		hr {
			border: 0;
			border-bottom: 1px solid #ccc;
		}

		pre {
			overflow: auto;
			text-align: left;
		}
	</style>
</head>
<body>
	<h1>Error</h1>
	<p>
		{$exception->getMessage()}<br>
		<small><code>{$exception->getFile()}:{$exception->getLine()}</code></small>
	</p>
	<hr>
	<pre>{$exception->getTraceAsString()}</pre>
</body>
</html>
HTML);
	}
}
