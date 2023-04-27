<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Contract;

use Closure;
use Elephox\Collection\Contract\GenericReadonlyList;
use Elephox\Web\Contract\WebMiddleware;

interface RouteData
{
	public function getLoader(): RouteLoader;

	public function getTemplate(): RouteTemplate;

	/**
	 * @return GenericReadonlyList<string>
	 */
	public function getMethods(): GenericReadonlyList;

	public function getHandlerName(): string;

	public function getHandler(): Closure;

	/**
	 * @return GenericReadonlyList<WebMiddleware>
	 */
	public function getMiddlewares(): GenericReadonlyList;

	public function getRegExp(): string;
}
