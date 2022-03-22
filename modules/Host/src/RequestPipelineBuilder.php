<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Collection\ArrayList;
use Elephox\Host\Contract\RequestPipelineEndpoint;
use Elephox\Host\Contract\WebMiddleware;

class RequestPipelineBuilder
{
	/** @var ArrayList<Contract\WebMiddleware> $pipeline  */
	private ArrayList $pipeline;

	public function __construct(
		private RequestPipelineEndpoint $endpoint,
	)
	{
		/** @var ArrayList<Contract\WebMiddleware> */
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

	public function addRouting(): void
	{
		$this->endpoint(new RequestRouter());
	}
}
