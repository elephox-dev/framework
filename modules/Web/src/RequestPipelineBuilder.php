<?php
declare(strict_types=1);

namespace Elephox\Web;

use Elephox\Collection\ArrayList;
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

	/**
	 * @param PipelineEndpoint|null $endpoint
	 * @param class-string<PipelineEndpoint>|null $endpointClass
	 */
	public function __construct(
		private ?PipelineEndpoint $endpoint,
		private ?string $endpointClass,
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
	 *
	 * @return WebMiddleware|class-string<WebMiddleware>
	 */
	public function pop(?string $className = null): WebMiddleware|string
	{
		$predicate = $className === null ? null : static fn (WebMiddleware|string $middleware): bool => $middleware === $className || $middleware instanceof $className;

		/** @psalm-suppress InvalidArgument */
		return $this->middlewares->pop($predicate);
	}

	public function endpoint(PipelineEndpoint|string $endpoint): self
	{
		if (is_string($endpoint)) {
			$interfaces = class_implements($endpoint);
			if ($interfaces === false || !in_array(PipelineEndpoint::class, $interfaces, true)) {
				throw new InvalidArgumentException('Given class name must implement ' . PipelineEndpoint::class);
			}

			/** @var class-string<PipelineEndpoint> $endpoint */
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
		/** @var int|null $key */
		$key = $this->middlewares->firstKeyOrDefault(null, static fn (string|WebMiddleware $middleware): bool => $middleware instanceof ExceptionHandler);

		if ($key === null) {
			$this->middlewares->insertAt(0, $exceptionHandler);
		} else {
			$this->middlewares->put($key, $exceptionHandler);
		}

		return $this;
	}

	public function build(Resolver $resolver): RequestPipeline
	{
		if ($this->endpoint === null && $this->endpointClass !== null) {
			$this->endpoint = $resolver->instantiate($this->endpointClass);
		} elseif ($this->endpoint === null) {
			throw new LogicException('Either an endpoint or the class name for an endpoint needs to be set');
		}

		assert($this->endpoint instanceof PipelineEndpoint, 'Invalid endpoint type, expected class implementing ' . PipelineEndpoint::class);

		/** @var ArrayList<WebMiddleware> $concreteMiddlewares */
		$concreteMiddlewares = new ArrayList();

		foreach ($this->middlewares as $middleware) {
			if (is_string($middleware)) {
				$concreteMiddleware = $resolver->instantiate($middleware);
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
