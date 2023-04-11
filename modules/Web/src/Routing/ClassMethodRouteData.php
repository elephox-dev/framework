<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Closure;
use Elephox\Collection\ArrayList;
use Elephox\DI\Contract\Resolver;
use Elephox\DI\Contract\ServiceCollection;
use Elephox\Http\Contract\RequestMethod;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Web\Routing\Contract\RouteLoader;
use Elephox\Web\Routing\Contract\RouteTemplate;
use ReflectionMethod;

readonly class ClassMethodRouteData extends AbstractRouteData
{
	private string $regExp;
	private string $className;
	private string $methodName;

	public function __construct(
		RouteLoader $loader,
		RouteTemplate|string $template,
		ArrayList|iterable $middlewares,
		RequestMethod|string|iterable $methods,
		protected ReflectionMethod $reflectionMethod,
	) {
		parent::__construct($loader, $template, $middlewares, $methods);

		$class = $this->reflectionMethod->getDeclaringClass();

		$this->className = $class->getName();
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
		return function (ServiceCollection $services, Resolver $resolver, RouteParametersMap $params): ResponseBuilder {
			if ($services->hasService($this->className)) {
				$controller = $services->requireService($this->className);
			} else {
				$controller = $resolver->instantiate($this->className);
			}

			return $resolver->callOn($controller, $this->methodName, $params->toArray());
		};
	}

	public function getRegExp(): string
	{
		return $this->regExp;
	}
}
