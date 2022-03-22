<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\DI\Contract\Resolver;
use Elephox\Host\Contract\WebHostEnvironment;
use Elephox\Host\Contract\WebServiceCollection as WebServiceCollectionContract;
use Elephox\Host\Middleware\ProcessingTimeHeader;
use Elephox\Http\Contract\Request as RequestContract;
use Elephox\Http\Contract\Response as ResponseContract;
use Elephox\Http\Contract\ServerRequest;
use Elephox\Http\Contract\ServerRequest as ServerRequestContract;
use Elephox\Http\ResponseSender;
use Elephox\Http\ServerRequestBuilder;

class WebApplication
{
	public function __construct(
		public readonly WebHostEnvironment $environment,
		public readonly WebServiceCollectionContract $services,
		public readonly ConfigurationRoot $configuration,
	)
	{
	}

	public static function createBuilder(): WebApplicationBuilder
	{
		$configuration = new ConfigurationManager();
		$environment = new GlobalWebHostEnvironment();
		$services = new WebServiceCollection();
		$pipeline = new RequestPipelineBuilder(new FallbackEndpoint());

		$pipeline->push(new ProcessingTimeHeader());

		return new WebApplicationBuilder(
			$configuration,
			$environment,
			$services,
			$pipeline,
		);
	}

	public function run(): void
	{
		/*
		 * 1. get request from globals
		 * 2. call handle()
		 * 3. send response to client
		 */

		/** @var ServerRequestContract $request */
		$request = $this->services
			->requireService(Resolver::class)
			->call(ServerRequestBuilder::class, 'fromGlobals');
		$this->services->addSingleton(ServerRequestContract::class, implementation: $request);

		$response = $this->handle($request);
		ResponseSender::sendResponse($response);
	}

	public function handle(RequestContract $request): ResponseContract
	{
		// remove any previous request instance
		$this->services->removeService(RequestContract::class);

		// add current request instance
		$this->services->addSingleton(RequestContract::class, implementation: $request);

		return $this->services
			->requireService(RequestPipeline::class)
			->process($request)
			->get();
	}
}
