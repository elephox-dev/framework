<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Core\Context\Contract\ExceptionContext;
use Elephox\Core\Context\RequestContext;
use Elephox\Core\Handler\Attribute\ExceptionHandler;
use Elephox\Host\Contract\Environment;
use Elephox\Host\GlobalEnvironment;
use Elephox\Http\GeneratesResponses;
use Elephox\Http\ResponseCode;
use Elephox\Http\ResponseSender;
use Elephox\Logging\Contract\Logger;
use Elephox\Mimey\MimeType;
use LogicException;
use Throwable;

#[ExceptionHandler(weight: -100)]
class DefaultExceptionHandler
{
	use GeneratesResponses;

	private ?ExceptionContext $context = null;

	public function getEnv(): Environment
	{
		if ($this->context === null) {
			throw new LogicException('Context is not set');
		}

		return $this->context->getContainer()->getOrRegister(Environment::class, GlobalEnvironment::class);
	}

	public function __invoke(ExceptionContext $context): void
	{
		$this->context = $context;

		$original = $this->context->getOriginal();
		if ($original instanceof RequestContext) {
			$this->handleRequestContext($context->getException());
		} else {
			$this->handleGeneralContext();
		}
	}

	private function handleGeneralContext(): void
	{
		if ($this->context === null) {
			throw new LogicException('Context is not set');
		}

		if ($this->context->getContainer()->has(Logger::class)) {
			$logger = $this->context->getContainer()->get(Logger::class);
		} else {
			$logger = new class {
				public function error(string $message, array $metaData = []): void
				{
					fwrite(STDERR, $message . PHP_EOL);
					if (array_key_exists('exception', $metaData)) {
						/** @var Throwable $exception */
						$exception = $metaData['exception'];
						fwrite(STDERR, $exception->getTraceAsString() . PHP_EOL);
					} else if (!empty($metaData)) {
						/** @var string $json */
						$json = json_encode($metaData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
						fwrite(STDERR, $json . PHP_EOL);
					}
				}
			};
		}

		$logger->error("An unhandled exception occurred: {$this->context->getException()->getMessage()}", [
			'exception' => $this->context->getException()
		]);
	}

	private function handleRequestContext(Throwable $exception): void
	{
		if ($this->getEnv()->isDevelopment()) {
			$boxContent = $this->getDebugBoxContent($exception);
		} else {
			$boxContent = $this->getProductionBoxContent();
		}

		ResponseSender::sendResponse(
			$this->stringResponse(<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css">
	<title>Internal Server Error</title>
</head>
<body>
	<section class="section">
		<div class="container">
			<p class="title is-1">Internal Server Error</p>

			<div class="box">
				$boxContent
			</div>
		</div>
	</section>
</body>
</html>
HTML,
				ResponseCode::InternalServerError,
				MimeType::TextHtml
			)
		);
	}

	private function getDebugBoxContent(Throwable $exception): string
	{
		return <<<HTML
<p class="title is-4">Summary</p>

<p>{$exception->getMessage()}</p>
<br>
<pre>{$exception->getTraceAsString()}</pre>
HTML;
	}

	private function getProductionBoxContent(): string
	{
		return <<<HTML
<p class="title is-4">An unexpected error occurred</p>

<p>
	That's all we know, unfortunately.
</p>

<hr>

<p>
	If you are the owner of this website, set <code>APP_ENV</code> to <code>local</code> to debug this error.
</p>
HTML;
	}
}
