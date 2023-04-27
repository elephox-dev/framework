<?php
declare(strict_types=1);

namespace Elephox\Web;

use Elephox\Configuration\Contract\Configuration;
use Elephox\DI\Contract\Resolver;
use Elephox\DI\Contract\ServiceProvider;
use Elephox\DI\Contract\ServiceScopeFactory;
use Elephox\Http\Contract\Request as RequestContract;
use Elephox\Http\Contract\Response as ResponseContract;
use Elephox\Http\Contract\ServerRequest as ServerRequestContract;
use Elephox\Http\ResponseSender;
use Elephox\Http\ServerRequestBuilder;
use Elephox\Support\Contract\ExceptionHandler;
use Elephox\Web\Contract\WebEnvironment;

class WebApplication
{
	protected ?ExceptionHandler $exceptionHandler = null;

	public function __construct(
		public readonly ServiceProvider $services,
		public readonly Configuration $configuration,
		public readonly WebEnvironment $environment,
		public readonly RequestPipeline $pipeline,
	) {
	}

	public function exceptionHandler(): ExceptionHandler
	{
		if ($this->exceptionHandler === null) {
			$this->exceptionHandler = $this->services->require(ExceptionHandler::class);
		}

		return $this->exceptionHandler;
	}

	public function run(): void
	{
		/** @var ServerRequestContract $request */
		$request = $this->services
			->require(Resolver::class)
			->callStaticMethod(ServerRequestBuilder::class, 'fromGlobals')
		;

		$response = $this->handle($request);
		ResponseSender::sendResponse($response);
	}

	public function handle(RequestContract $request): ResponseContract
	{
		$requestScope = $this->services->require(ServiceScopeFactory::class)->createScope();

		// TODO: use services from scoped service provider
		$response = $this->pipeline->process($request)->get();

		$requestScope->endScope();

		return $response;
	}
}
