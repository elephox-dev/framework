<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Closure;
use Elephox\Collection\ArrayList;
use Elephox\DI\Contract\Resolver;
use Elephox\Http\Contract\RequestMethod;
use Elephox\Web\Routing\Contract\RouteLoader;
use Elephox\Web\Routing\Contract\RouteTemplate;
use ReflectionException;
use ReflectionFunction;

readonly class ClosureRouteData extends AbstractRouteData
{
	private string $regExp;

	public function __construct(
		RouteLoader $loader,
		RouteTemplate|string $template,
		ArrayList|iterable $middlewares,
		RequestMethod|string|iterable $methods,
		protected Closure $closure,
	) {
		parent::__construct($loader, $template, $middlewares, $methods);

		$this->regExp = $this->getTemplate()->renderRegExp([]);
	}

	public function getHandlerName(): string
	{
		try {
			return (new ReflectionFunction($this->closure))->getName();
		} catch (ReflectionException $e) {
			$class = $e::class;

			return "$class: {$e->getMessage()}";
		}
	}

	public function getHandler(): Closure
	{
		return fn (Resolver $resolver, RouteParametersMap $params) => $resolver->callback($this->closure, $params->toArray());
	}

	public function getRegExp(): string
	{
		return $this->regExp;
	}
}
