<?php
declare(strict_types=1);

namespace Elephox\Web;

use Elephox\Collection\ArrayList;
use Elephox\Web\Contract\RequestPipelineEndpoint;
use Elephox\Web\Contract\WebMiddleware;
use Elephox\Web\Endpoint\RequestRouter;
use Elephox\Web\Middleware\WhoopsExceptionHandler;

class RequestPipelineBuilder
{
	/** @var ArrayList<\Elephox\Web\Contract\WebMiddleware> $pipeline  */
	private ArrayList $pipeline;

	public function __construct(
		private RequestPipelineEndpoint $endpoint,
	)
	{
		/** @var ArrayList<\Elephox\Web\Contract\WebMiddleware> */
		$this->pipeline = new ArrayList();
	}

	/**
	 * @param WebMiddleware $middleware
	 *
	 * @return RequestPipelineBuilder
	 */
	public function push(WebMiddleware $middleware): RequestPipelineBuilder
	{
		$this->pipeline->add($middleware);

		return $this;
	}

	public function pop(): WebMiddleware
	{
		return $this->pipeline->pop();
	}

	public function endpoint(RequestPipelineEndpoint $endpoint): RequestPipelineBuilder
	{
		$this->endpoint = $endpoint;

		return $this;
	}

	public function build(): RequestPipeline
	{
		return new RequestPipeline($this->endpoint, $this->pipeline);
	}
}
