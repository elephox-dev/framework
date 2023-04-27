<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Closure;
use Elephox\Collection\ArrayList;
use Elephox\DI\Contract\Resolver;
use Elephox\DI\Contract\ServiceProvider;
use Elephox\Http\Contract\RequestMethod;
use Elephox\Http\Contract\RequestMethod as RequestMethodContract;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Web\Contract\WebMiddleware;
use Elephox\Web\Routing\Contract\RouteLoader;
use Elephox\Web\Routing\Contract\RouteTemplate;
use ReflectionMethod;

readonly class ClassMethodRouteData extends AbstractRouteData
{
	private string $regExp;

	/**
	 * @var class-string $className
	 */
	private string $className;

	/**
	 * @var non-empty-string $methodName
	 */
	private string $methodName;

	/**
	 * @param ArrayList<WebMiddleware>|iterable<WebMiddleware> $middlewares
	 * @param RequestMethodContract|string|iterable<mixed, RequestMethodContract|non-empty-string> $methods
	 */
	public function __construct(
		RouteLoader $loader,
		RouteTemplate|string $template,
		ArrayList|iterable $middlewares,
		RequestMethod|string|iterable $methods,
		protected ReflectionMethod $reflectionMethod,
	) {
		parent::__construct($loader, $template, $middlewares, $methods);

		/** @var class-string */
		$this->className = $this->reflectionMethod->getDeclaringClass()->getName();

		/** @var non-empty-string */
		$this->methodName = $this->reflectionMethod->getName();

		$this->regExp = $this->getTemplate()->renderRegExp([
			'controller' => $this->className,
			'action' => $this->methodName,
		]);
	}

	public function getHandlerName(): string
	{
		return "$this->className::$this->methodName";
	}

	public function getHandler(): Closure
	{
		return $this->__invoke(...);
	}

	public function __invoke(ServiceProvider $services, Resolver $resolver, RouteParametersMap $params): ResponseBuilder
	{
		if ($services->has($this->className)) {
			$controller = $services->require($this->className);
		} else {
			$controller = $resolver->instantiate($this->className);
		}

		/** @var array<non-empty-string, mixed> $args */
		$args = $params->toArray();

		/** @var ResponseBuilder */
		return $resolver->callMethodOn($controller, $this->methodName, $args);
	}

	public function getRegExp(): string
	{
		return $this->regExp;
	}
}
