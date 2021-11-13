<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Core\Handler\Contract;
use Elephox\DI\Contract\Container;
use Elephox\Http\Contract\Response;
use ReflectionClass;
use ReflectionMethod;

class Handlers
{
	/** @var null|Contract\HandlerContainer  */
	private static ?Contract\HandlerContainer $handlerContainer = null;

	/**
	 * @returns Contract\HandlerContainer<object>
	 */
	private static function getHandlerContainer(): Contract\HandlerContainer
	{
		if (self::$handlerContainer === null) {
			self::$handlerContainer = new HandlerContainer();
		}

		return self::$handlerContainer;
	}

	/**
	 * @throws \ReflectionException
	 */
	public static function load(Container $container): void
	{
		$classes = get_declared_classes();
		foreach ($classes as $class) {
			$reflection = new ReflectionClass($class);
			$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
			foreach ($methods as $method) {
				$handlerAttributes = $method->getAttributes(HandlerAttribute::class);
				if (empty($handlerAttributes)) {
					continue;
				}

				$handler = $container->instantiate($class);

				foreach ($handlerAttributes as $handlerAttribute) {
					/** @var HandlerAttribute $attribute */
					$attribute = $handlerAttribute->newInstance();
					$binding = new HandlerBinding($handler, $method->getName(), $attribute->getType());
					self::getHandlerContainer()->register($binding);
				}
			}
		}
	}

	public static function handle(Contract\Context $context): void
	{
		$binding = self::getHandlerContainer()->findHandler($context);
		$handler = $binding->getHandler();
		$method = $binding->getMethodName();

		$result = $context->getContainer()->call($handler, $method, ['context' => $context]);

		if ($result instanceof Response) {
			/** @var Response $result */
			$result->send();
		}
	}
}
