<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\Host\Contract\WebHostEnvironment;
use Elephox\Host\Contract\WebServiceCollection as WebServiceCollectionContract;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\Response;

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

		return new WebApplicationBuilder(
			$configuration,
			$environment,
			$services,
		);
	}

	public function run(): void
	{
		// TODO: Implement run() method.

		/*
		 * 1. get request from globals
		 * 2. call handle()
		 * 4. send response to client
		 */
	}

	public function handle(Request $request): Response
	{
		// TODO: Implement handle() method.

		/*
		 * 1. find appropriate handler for request
		 * 2. build and run callstack
		 * 3. return response
		 */
	}
}
