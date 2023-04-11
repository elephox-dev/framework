<?php
declare(strict_types=1);

namespace Elephox\Web;

use Elephox\Collection\ArrayList;
use Elephox\Collection\EmptySequenceException;
use Elephox\DI\Contract\Resolver;
use Elephox\Support\Contract\ExceptionHandler;
use Elephox\Web\Contract\PipelineEndpoint;
use Elephox\Web\Contract\WebMiddleware;
use InvalidArgumentException;
use LogicException;

class RequestPipelineBuilder
{
	/**
	 * @var ArrayList<WebMiddleware|class-string<WebMiddleware>> $middlewares
	 */
	private ArrayList $middlewares;

	public function __construct(
		private ?PipelineEndpoint $endpoint,
		private ?string $endpointClass,
		private readonly Resolver $resolver,
	) {
		/** @var ArrayList<WebMiddleware|class-string<WebMiddleware>> */
		$this->middlewares = new ArrayList();
	}

	/**
	 * @param class-string<WebMiddleware>|WebMiddleware $middleware
	 */
	public function push(WebMiddleware|string $middleware): self
	{
		$this->middlewares->add($middleware);

		return $this;
	}

	/**
	 * @param class-string<WebMiddleware>|null $className
	 */
	public function pop(?string $className = null): WebMiddleware
	{
		$predicate = $className === null ? null : static fn (WebMiddleware $middleware): bool => $middleware instanceof $className;

		return $this->middlewares->pop($predicate);
	}

	public function endpoint(PipelineEndpoint|string $endpoint): self
	{
		if (is_string($endpoint)) {
			$this->endpoint = null;
			$this->endpointClass = $endpoint;
		} else {
			$this->endpoint = $endpoint;
			$this->endpointClass = null;
		}

		return $this;
	}

	public function exceptionHandler(WebMiddleware&ExceptionHandler $exceptionHandler): self
	{
		try {
			/** @var int $key */
			$key = $this->middlewares->firstKey(static fn (string|WebMiddleware $middleware): bool => $middleware instanceof ExceptionHandler);

			$this->middlewares->put($key, $exceptionHandler);
		} catch (EmptySequenceException) {
			$this->middlewares->insertAt(0, $exceptionHandler);
		}

		return $this;
	}

	public function build(): RequestPipeline
	{
		if ($this->endpoint === null && $this->endpointClass === null) {
			throw new LogicException('Either an endpoint or the class name for an endpoint needs to be set');
		}

		$this->endpoint ??= $this->resolver->instantiate($this->endpointClass);

		assert($this->endpoint instanceof PipelineEndpoint);

		/** @var ArrayList<WebMiddleware> $concreteMiddlewares */
		$concreteMiddlewares = new ArrayList();

		foreach ($this->middlewares as $middleware) {
			if (is_string($middleware)) {
				$concreteMiddleware = $this->resolver->instantiate($middleware);
				if (!($concreteMiddleware instanceof WebMiddleware)) {
					throw new InvalidArgumentException("Given middleware '$middleware' does not implement " . WebMiddleware::class);
				}

				$concreteMiddlewares->add($concreteMiddleware);
			} else {
				$concreteMiddlewares->add($middleware);
			}
		}

		return new RequestPipeline($this->endpoint, $concreteMiddlewares);
	}
}
