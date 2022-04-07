<?php
declare(strict_types=1);

namespace Elephox\Web;

use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\DI\Contract\Resolver;
use Elephox\DI\Contract\ServiceCollection as ServiceCollectionContract;
use Elephox\Http\Contract\Request as RequestContract;
use Elephox\Http\Contract\Response as ResponseContract;
use Elephox\Http\Contract\ServerRequest as ServerRequestContract;
use Elephox\Http\ResponseSender;
use Elephox\Http\ServerRequestBuilder;
use Elephox\Web\Contract\WebEnvironment;

class WebApplication
{
	public function __construct(
		public readonly WebEnvironment $environment,
		public readonly ServiceCollectionContract $services,
		public readonly ConfigurationRoot $configuration,
	) {
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
		// remove any previous request instance
		$this->services->removeService(RequestContract::class);

		// add current request instance
		$this->services->addSingleton(RequestContract::class, implementation: $request);

		return $this->services
			->requireService(RequestPipeline::class)
			->process($request)
			->get()
		;
	}
}
