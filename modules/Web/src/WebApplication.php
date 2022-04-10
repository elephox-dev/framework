<?php
declare(strict_types=1);

namespace Elephox\Web;

use Elephox\Configuration\Contract\Configuration;
use Elephox\DI\Contract\Resolver;
use Elephox\DI\Contract\ServiceCollection as ServiceCollectionContract;
use Elephox\Http\Contract\Request as RequestContract;
use Elephox\Http\Contract\Response as ResponseContract;
use Elephox\Http\Contract\ServerRequest as ServerRequestContract;
use Elephox\Http\ResponseSender;
use Elephox\Http\ServerRequestBuilder;
use Elephox\Logging\Contract\Logger;
use Elephox\Support\Contract\ExceptionHandler;
use Elephox\Web\Contract\WebEnvironment;

class WebApplication
{
	protected ?Logger $logger = null;
	protected ?ExceptionHandler $exceptionHandler = null;

	public function __construct(
		public readonly ServiceCollectionContract $services,
		public readonly Configuration $configuration,
		public readonly WebEnvironment $environment,
		public readonly RequestPipeline $pipeline,
	) {
		$this->services->addSingleton(__CLASS__, implementation: $this);
	}

	public function logger(): Logger
	{
		if ($this->logger === null) {
			$this->logger = $this->services->requireService(Logger::class);
		}

		return $this->logger;
	}

	public function exceptionHandler(): ExceptionHandler
	{
		if ($this->exceptionHandler === null) {
			$this->exceptionHandler = $this->services->requireService(ExceptionHandler::class);
		}

		return $this->exceptionHandler;
	}

	public function run(): void
	{
		/** @var ServerRequestContract $request */
		$request = $this->services
			->requireService(Resolver::class)
			->call(ServerRequestBuilder::class, 'fromGlobals')
		;

		$response = $this->handle($request);
		ResponseSender::sendResponse($response);
	}

	public function handle(RequestContract $request): ResponseContract
	{
		$this->services->addSingleton(RequestContract::class, implementation: $request, replace: true);

		return $this->pipeline->process($request)->get();
	}
}
