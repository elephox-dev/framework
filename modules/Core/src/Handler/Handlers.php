<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Handler\Attribute\AbstractHandler;
use Elephox\Core\Handler\Contract;
use Elephox\DI\Contract\Container;
use Elephox\Http\Contract\Response;
use Exception;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Handlers
{
	/**
	 * @throws ReflectionException
	 * @throws \Exception
	 */
	public static function load(Container $container): void
	{
		if (!$container->has(Contract\HandlerContainer::class)) {
			$container->register(Contract\HandlerContainer::class, new HandlerContainer());
		}

		$handlerContainer = $container->get(Contract\HandlerContainer::class);

		/** @var null|class-string<Contract\ComposerAutoloaderInit> $autoloaderClassName */
		$autoloaderClassName = null;
		foreach (get_declared_classes() as $class) {
			if (!str_starts_with($class, 'ComposerAutoloaderInit')) {
				continue;
			}

			$autoloaderClassName = $class;

			break;
		}

		if ($autoloaderClassName === null) {
			throw new Exception('Could not find ComposerAutoloaderInit class. Did you install the dependencies using composer?');
		}

		$classLoader = call_user_func([$autoloaderClassName, 'getLoader']);
		foreach ($classLoader->getClassMap() as $class => $path) {
			if (!str_starts_with($class, 'App\\')) {
				continue;
			}

			require_once $path;
		}

		foreach (get_declared_classes() as $class) {
			if (!str_starts_with($class, 'App\\')) {
				continue;
			}

			$reflection = new ReflectionClass($class);
			$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
			foreach ($methods as $method) {
				$handlerAttributes = $method->getAttributes(AbstractHandler::class, ReflectionAttribute::IS_INSTANCEOF);
				if (empty($handlerAttributes)) {
					continue;
				}

				$handler = $container->instantiate($class);

				foreach ($handlerAttributes as $handlerAttribute) {
					$attribute = $handlerAttribute->newInstance();
					$binding = new HandlerBinding($handler, $method->getName(), $attribute);
					$handlerContainer->register($binding);
				}
			}
		}
	}

	/**
	 * @throws \Exception
	 */
	public static function handle(Context $context): void
	{
		$binding = $context->getContainer()->get(Contract\HandlerContainer::class)->findHandler($context);

		$binding->handle($context);
	}
}
