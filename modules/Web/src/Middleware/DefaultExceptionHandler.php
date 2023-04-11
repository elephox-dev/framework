<?php
declare(strict_types=1);

namespace Elephox\Web\Middleware;

use Closure;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Http\Response;
use Elephox\Http\ResponseSender;
use Elephox\Support\Contract\ExceptionHandler;
use Elephox\Web\Contract\WebMiddleware;
use Throwable;

class DefaultExceptionHandler implements WebMiddleware, ExceptionHandler
{
	public function handle(Request $request, Closure $next): ResponseBuilder
	{
		$response = $next($request);

		if ($this->shouldHandle($response)) {
			$this->setResponseBody($response);
		}

		return $response;
	}

	protected function shouldHandle(ResponseBuilder $response): bool
	{
		return $response->getException() !== null && $response->getBody() === null;
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

		$exceptionClass = $exception::class;

		return $response->htmlBody(<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Unhandled Exception: {$exception->getMessage()}</title>
	<style rel="stylesheet">
		html, body {
			margin: 1rem;
			text-align: center;
			font-family: sans-serif;
		}

		@media (prefers-color-scheme: dark) {
			body {
				background: #202124;
				color: #fff;
			}
		}

		hr {
			border: 0;
			border-bottom: 1px solid #ccc;
		}

		pre {
			overflow: auto;
			text-align: left;
			padding: 1rem;
		}
	</style>
</head>
<body>
	<h1>Error</h1>
	<p>
		<small><code>{$exceptionClass}</code></small><br>
		<strong>{$exception->getMessage()}</strong><br>
		<small>thrown at: <code>{$exception->getFile()}:{$exception->getLine()}</code></small>
	</p>
	<hr>
	<pre>{$exception->getTraceAsString()}</pre>
</body>
</html>
HTML);
	}
}
