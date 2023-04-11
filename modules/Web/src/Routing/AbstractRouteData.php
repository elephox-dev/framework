<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericReadonlyList;
use Elephox\Http\Contract\RequestMethod as RequestMethodContract;
use Elephox\Web\Contract\WebMiddleware;
use Elephox\Web\Routing\Contract\RouteData;
use Elephox\Web\Routing\Contract\RouteLoader;
use Elephox\Web\Routing\Contract\RouteTemplate as RouteTemplateContract;

abstract readonly class AbstractRouteData implements RouteData
{
	private RouteTemplateContract $template;

	/**
	 * @var ArrayList<WebMiddleware> $middlewares
	 */
	private ArrayList $middlewares;

	/**
	 * @var ArrayList<string> $methods
	 */
	private ArrayList $methods;

	public function __construct(
		private RouteLoader $loader,
		RouteTemplateContract|string $template,
		ArrayList|iterable $middlewares,
		RequestMethodContract|string|iterable $methods,
	) {
		if (is_string($template)) {
			$this->template = RouteTemplate::parse($template);
		} else {
			$this->template = $template;
		}

		if ($middlewares instanceof ArrayList) {
			$this->middlewares = $middlewares;
		} else {
			$this->middlewares = new ArrayList();
			foreach ($middlewares as $middleware) {
				assert($middleware instanceof WebMiddleware);

				$this->middlewares->add($middleware);
			}
		}

		if (is_string($methods) || $methods instanceof RequestMethodContract) {
			$methods = [$methods];
		}

		assert(is_iterable($methods));

		$this->methods = new ArrayList();
		foreach ($methods as $methodOrString) {
			if (is_string($methodOrString)) {
				$verb = $methodOrString;
			} else {
				assert($methodOrString instanceof RequestMethodContract);

				$verb = $methodOrString->getValue();
			}

			$this->methods->add($verb);
		}
	}

	public function getLoader(): RouteLoader
	{
		return $this->loader;
	}

	public function getTemplate(): RouteTemplateContract
	{
		return $this->template;
	}

	public function getMiddlewares(): GenericReadonlyList
	{
		return $this->middlewares;
	}

	public function getMethods(): GenericReadonlyList
	{
		return $this->methods;
	}
}
