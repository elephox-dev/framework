<?php
declare(strict_types=1);

namespace Elephox\Host;

use Closure;
use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\DI\Contract\Resolver;
use Elephox\Host\Contract\WebHostEnvironment;
use Elephox\Host\Contract\WebMiddleware;
use Elephox\Host\Contract\WebServiceCollection as WebServiceCollectionContract;
use Elephox\Http\Contract\Request as RequestContract;
use Elephox\Http\Contract\Response as ResponseContract;
use Elephox\Http\Contract\ResponseBuilder as ResponseBuilderContract;
use Elephox\Http\Contract\ServerRequest;
use Elephox\Http\Contract\ServerRequest as ServerRequestContract;
use Elephox\Http\ParameterSource;
use Elephox\Http\Response;
use Elephox\Http\ResponseCode;
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
		$pipeline = new RequestPipelineBuilder();

		$pipeline->push(new class implements WebMiddleware {
			public function handle(RequestContract $request, Closure $next): ResponseBuilderContract
			{
				$responseBuilder = $next($request);
				if ($request instanceof ServerRequestContract) {
					$requestStart = (float)$request->getParameters()->get('REQUEST_TIME_FLOAT', ParameterSource::Server);
					$now = microtime(true);
					$diff = round($now - $requestStart, 5);
					$responseBuilder->header('X-Processing-Time', [(string)$diff]);
				}

				return $responseBuilder;
			}
		});

		$pipeline->push(new class implements WebMiddleware {
			public function handle(RequestContract $request, Closure $next): ResponseBuilderContract
			{
				$responseBuilder = $next($request);
				if ($responseBuilder->getResponseCode() === null) {
					$responseBuilder->responseCode(ResponseCode::NotFound);
				}

				return $responseBuilder;
			}
		});

		return new WebApplicationBuilder(
			$configuration,
			$environment,
			$services,
			$pipeline,
		);
	}

	public function addRouting(): void
	{
		$pipeline = $this->services->requireService(RequestPipelineBuilder::class);
		$pipeline->push(new class implements WebMiddleware {
			public function handle(RequestContract $request, Closure $next): ResponseBuilderContract
			{
				$url = (string)$request->getUrl();

			}
		});
	}

	public function run(): void
	{
		/*
		 * 1. get request from globals
		 * 2. call handle()
		 * 3. send response to client
		 */

		$resolver = $this->services->requireService(Resolver::class);

		/** @var ServerRequestContract $request */
		$request = $resolver->call(ServerRequestBuilder::class, 'fromGlobals');
		$this->services->addSingleton(ServerRequestContract::class, $request::class, implementation: $request);

		$response = $this->handle($request);
		ResponseSender::sendResponse($response);
	}

	public function handle(RequestContract $request): ResponseContract
	{
		// remove any previous request instance
		$this->services->removeService(RequestContract::class);

		// add current request instance
		$this->services->addSingleton(RequestContract::class, $request::class, implementation: $request);

		return $this->services
			->requireService(RequestPipeline::class)
			->process(static fn (): ResponseBuilderContract => Response::build(), $request)
			->get();
	}
}
