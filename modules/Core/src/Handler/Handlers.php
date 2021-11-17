<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Handler\Attribute\AbstractHandler;
use Elephox\Core\Handler\Contract;
use Elephox\DI\Contract\Container;
use Exception;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

class Handlers
{
	/**
	 * @throws \ReflectionException
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

		/** @var Contract\ComposerClassLoader $classLoader */
		$classLoader = call_user_func([$autoloaderClassName, 'getLoader']);

		// TODO: find a better way to load the App\ namespace

		$classLoader->loadClass("App\\App");
		foreach (array_keys($classLoader->getClassMap()) as $class) {
			if (!str_starts_with($class, 'App\\')) {
				continue;
			}

			if ($classLoader->loadClass($class) === null) {
				throw new Exception('Could not load class ' . $class);
			}
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
