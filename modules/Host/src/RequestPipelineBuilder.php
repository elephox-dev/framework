<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Collection\ArrayList;
use Elephox\Host\Contract\WebMiddleware;

class RequestPipelineBuilder
{
	/** @var ArrayList<Contract\WebMiddleware> $pipeline  */
	private ArrayList $pipeline;

	public function __construct()
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

	public function build(): RequestPipeline
	{
		return new RequestPipeline($this->pipeline);
	}
}
