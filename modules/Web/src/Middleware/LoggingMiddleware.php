<?php
declare(strict_types=1);

namespace Elephox\Web\Middleware;

use Closure;
use Elephox\Configuration\Contract\Environment;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Http\Contract\ServerRequest;
use Elephox\Http\ParameterSource;
use Elephox\Logging\Contract\LogLevel as LogLevelContract;
use Elephox\Logging\LogLevel;
use Elephox\Web\Contract\WebMiddleware;
use Psr\Log\LoggerInterface;

class LoggingMiddleware implements WebMiddleware
{
	public function __construct(private readonly LoggerInterface $logger, private readonly Environment $environment)
	{
	}

	public function handle(Request $request, Closure $next): ResponseBuilder
	{
		$response = $next($request);

		$this->logger->log($this->getLevel($request, $response), $this->getMessage($request, $response), $this->getContext($request, $response));

		return $response;
	}

	protected function getLevel(Request $request, ResponseBuilder $response): LogLevelContract
	{
		return $response->getException() !== null ? LogLevel::ERROR : LogLevel::DEBUG;
	}

	protected function getMessage(Request $request, ResponseBuilder $response): string
	{
		return sprintf('[%s] %s', $response->getResponseCode()?->value ?? 'unknown', $request->getUrl()->path);
	}

	protected function getContext(Request $request, ResponseBuilder $response): array
	{
		$data = [
			'request' => [
				'method' => $request->getMethod(),
				'url' => $request->getUrl()->toArray(),
				'headers' => $request->getHeaderMap()->toArray(),
				'protocol_version' => $request->getProtocolVersion(),
				'body' => htmlspecialchars($request->getBody()->getContents()),
			],
			'response' => [
				'code' => $response->getResponseCode(),
				'content_type' => $response->getContentType(),
				'headers' => $response->getHeaderMap()?->toArray(),
				'protocol_version' => $response->getProtocolVersion(),
				'body' => htmlspecialchars($response->getBody()?->getContents() ?? ''),
			],
			'environment' => $this->environment->asEnumerable()->toArray(),
		];

		if ($request instanceof ServerRequest) {
			// TODO: improve context for ServerRequest instances

			$data['request']['cookies'] = $request->getCookies()->toArray();
			$data['request']['server'] = $request->getParameters()->allFrom(ParameterSource::Server)->toArray();
			$data['request']['post'] = $request->getParameters()->allFrom(ParameterSource::Post)->toArray();
			$data['request']['session'] = $request->getSession()?->toArray();
			$data['request']['files'] = $request->getUploadedFiles()->toArray();
		}

		if ($exception = $response->getException()) {
			$data['exception'] = [
				'class' => $exception::class,
				'message' => $exception->getMessage(),
				'code' => $exception->getCode(),
				'file' => $exception->getFile(),
				'line' => $exception->getLine(),
				'trace' => $exception->getTrace(),
			];
		}

		return $data;
	}
}
