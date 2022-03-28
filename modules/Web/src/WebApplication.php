<?php
declare(strict_types=1);

namespace Elephox\Web;

use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\DI\Contract\Resolver;
use Elephox\Host\ConfigurationManager;
use Elephox\Http\Contract\Request as RequestContract;
use Elephox\Http\Contract\Response as ResponseContract;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Http\Contract\ServerRequest;
use Elephox\Http\Contract\ServerRequest as ServerRequestContract;
use Elephox\Http\Response;
use Elephox\Http\ResponseCode;
use Elephox\Http\ResponseSender;
use Elephox\Http\ServerRequestBuilder;
use Elephox\Web\Contract\RequestPipelineEndpoint;
use Elephox\Web\Contract\WebHostEnvironment;
use Elephox\Web\Contract\WebServiceCollection as WebServiceCollectionContract;
use Elephox\Web\Middleware\ProcessingTimeHeader;
use JetBrains\PhpStorm\ArrayShape;

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
		$pipeline = new RequestPipelineBuilder(new class implements RequestPipelineEndpoint {
			public function handle(RequestContract $request): ResponseBuilder
			{
				return Response::build()->responseCode(ResponseCode::NotFound);
			}
		});

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

	/**
	 * @return array{environment: WebHostEnvironment, services: WebServiceCollectionContract, configuration: ConfigurationRoot}
	 */
	public function __serialize(): array
	{
		return [
			'environment' => $this->environment,
			'services' => $this->services,
			'configuration' => $this->configuration,
		];
	}

	/**
	 * @param array{environment: WebHostEnvironment, services: WebServiceCollectionContract, configuration: ConfigurationRoot} $data
	 *
	 * @noinspection PhpSecondWriteToReadonlyPropertyInspection
	 */
	public function __unserialize(array $data): void
	{
		$this->environment = $data['environment'];
		$this->services = $data['services'];
		$this->configuration = $data['configuration'];
	}
}
