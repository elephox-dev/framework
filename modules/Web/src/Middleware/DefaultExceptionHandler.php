<?php
declare(strict_types=1);

namespace Elephox\Web\Middleware;

use Closure;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Stream\StringStream;
use Elephox\Web\Contract\WebMiddleware;

class DefaultExceptionHandler implements WebMiddleware
{
	public function handle(Request $request, Closure $next): ResponseBuilder
	{
		$response = $next($request);

		if ($exception = $response->getException()) {
			$response->body(new StringStream(<<<HTML
<!DOCTYPE html>
<html>
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
HTML));
		}

		return $response;
	}
}
