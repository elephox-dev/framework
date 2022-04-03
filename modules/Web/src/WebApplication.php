<?php
declare(strict_types=1);

namespace Elephox\Web;

use Elephox\Configuration\ConfigurationManager;
use Elephox\Configuration\Contract\ConfigurationManager as ConfigurationManagerContract;
use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\DI\Contract\Resolver;
use Elephox\DI\Contract\ServiceCollection as ServiceCollectionContract;
use Elephox\DI\ServiceCollection;
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
use Elephox\Web\Contract\WebEnvironment;
use Elephox\Web\Middleware\ProcessingTimeHeader;

class WebApplication
{
	public function __construct(
		public readonly WebEnvironment $environment,
		public readonly ServiceCollectionContract $services,
		public readonly ConfigurationRoot $configuration,
	)
	{
	}

	public static function createBuilder(
		?ConfigurationManagerContract $configuration = null,
		?WebEnvironment $environment = null,
		?ServiceCollectionContract $services = null,
		?RequestPipelineBuilder $pipeline = null,
	): WebApplicationBuilder
	{
		$configuration ??= new ConfigurationManager();
		$environment ??= new GlobalWebEnvironment();
		$services ??= new ServiceCollection();
		$pipeline ??= new RequestPipelineBuilder(new class implements RequestPipelineEndpoint {
			public function handle(RequestContract $request): ResponseBuilder
			{
				return Response::build()->responseCode(ResponseCode::BadRequest);
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
