<?php
declare(strict_types=1);

namespace Elephox\Web;

use Elephox\Collection\ArrayList;
use Elephox\Web\Contract\RequestPipelineEndpoint;
use Elephox\Web\Contract\WebMiddleware;

class RequestPipelineBuilder
{
	/** @var ArrayList<WebMiddleware> $pipeline  */
	private ArrayList $pipeline;

	public function __construct(
		private RequestPipelineEndpoint $endpoint,
	)
	{
		/** @var ArrayList<WebMiddleware> */
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

	/**
	 * @param class-string<WebMiddleware>|null $className
	 * @return WebMiddleware
	 */
	public function pop(?string $className = null): WebMiddleware
	{
		$predicate = $className === null ? null : static fn (WebMiddleware $middleware): bool => $middleware instanceof $className;

		return $this->pipeline->pop($predicate);
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
