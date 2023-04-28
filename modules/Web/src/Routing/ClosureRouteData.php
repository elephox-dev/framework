<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Closure;
use Elephox\Collection\ArrayList;
use Elephox\DI\Contract\Resolver;
use Elephox\Http\Contract\RequestMethod as RequestMethodContract;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Web\Contract\WebMiddleware;
use Elephox\Web\Routing\Contract\RouteLoader;
use Elephox\Web\Routing\Contract\RouteTemplate;
use ReflectionException;
use ReflectionFunction;

readonly class ClosureRouteData extends AbstractRouteData
{
	private string $regExp;

	/**
	 * @param ArrayList<WebMiddleware>|iterable<WebMiddleware> $middlewares
	 * @param RequestMethodContract|string|iterable<mixed, RequestMethodContract|non-empty-string> $methods
	 */
	public function __construct(
		RouteLoader $loader,
		RouteTemplate|string $template,
		ArrayList|iterable $middlewares,
		RequestMethodContract|string|iterable $methods,
		protected Closure $closure,
	) {
		parent::__construct($loader, $template, $middlewares, $methods);

		$this->regExp = $this->getTemplate()->renderRegExp([]);
	}

	public function getHandlerName(): string
	{
		try {
			$reflection = new ReflectionFunction($this->closure);
			if ($reflection->isClosure()) {
				return sprintf('%s @ %s:%d-%d', $reflection->getName(), $reflection->getFileName(), $reflection->getStartLine(), $reflection->getEndLine());
			}

			return sprintf('%s::%s', $reflection->getClosureScopeClass()?->getName() ?? '<unknown>', $reflection->getName());
		} catch (ReflectionException $e) {
			$class = $e::class;

			return "$class: {$e->getMessage()}";
		}
	}

	public function getHandler(): Closure
	{
		return $this->__invoke(...);
	}

	public function __invoke(Resolver $resolver, RouteParametersMap $params): ResponseBuilder
	{
		/** @var array<non-empty-string, mixed> $args */
		$args = $params->toArray();

		/** @var ResponseBuilder */
		return $resolver->call($this->closure, $args);
	}

	public function getRegExp(): string
	{
		return $this->regExp;
	}
}
