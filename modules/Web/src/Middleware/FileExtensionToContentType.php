<?php
declare(strict_types=1);

namespace Elephox\Web\Middleware;

use Closure;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Mimey\MimeType;
use Elephox\Web\Contract\WebMiddleware;
use InvalidArgumentException;

class FileExtensionToContentType implements WebMiddleware
{
	public function handle(Request $request, Closure $next): ResponseBuilder
	{
		$response = $next($request);

		if (!($response->getResponseCode()?->isSuccessful() ?? false)) {
			return $response;
		}

		$ext = pathinfo($request->getUrl()->path, PATHINFO_EXTENSION);
		if (!empty($ext)) {
			try {
				$response->contentType(MimeType::fromExtension($ext));
			} catch (InvalidArgumentException) {
			}
		}

		return $response;
	}
}
