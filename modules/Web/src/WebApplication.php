<?php
declare(strict_types=1);

namespace Elephox\Web;

use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\DI\Contract\Resolver;
use Elephox\Host\ConfigurationManager;
use Elephox\Host\Contract\ConfigurationManager as ConfigurationManagerContract;
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

class WebApplication
{
	public function __construct(
		public readonly WebHostEnvironment $environment,
		public readonly WebServiceCollectionContract $services,
		public readonly ConfigurationRoot $configuration,
	)
	{
	}

	public static function createBuilder(
		?ConfigurationManagerContract $configuration = null,
		?WebHostEnvironment $environment = null,
		?WebServiceCollectionContract $services = null,
		?RequestPipelineBuilder $pipeline = null,
	): WebApplicationBuilder
	{
		$configuration ??= new ConfigurationManager();
		$environment ??= new GlobalWebHostEnvironment();
		$services ??= new WebServiceCollection();
		$pipeline ??= new RequestPipelineBuilder(new class implements RequestPipelineEndpoint {
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
		/** @var ServerRequestContract $request */
		$request = $this->services
			->requireService(Resolver::class)
			->call(ServerRequestBuilder::class, 'fromGlobals');

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
