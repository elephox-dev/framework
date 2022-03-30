<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Autoloading\Composer\NamespaceLoader;
use Elephox\Collection\ObjectMap;
use Elephox\Console\Command\Contract\CommandHandler;
use Elephox\DI\Contract\Resolver;

class CommandCollection
{
	/** @var ObjectMap<CommandTemplate, CommandHandler> $templateMap */
	private ObjectMap $templateMap;

	public function __construct(private Resolver $resolver)
	{
		$this->templateMap = new ObjectMap();
	}

	public function add(CommandTemplate $template, CommandHandler $handler): void
	{
		$this->templateMap->put($template, $handler);
	}

	public function loadFromNamespace(string $namespace): void
	{
		NamespaceLoader::iterateNamespace($namespace, function(string $className): void {
			$this->loadFromClass($className);
		});
	}

	/**
	 * @param class-string $className
	 * @return void
	 */
	public function loadFromClass(string $className): void
	{
		$interfaces = class_implements($className);
		if (!$interfaces || !in_array(CommandHandler::class, $interfaces, true)) {
			return;
		}

		/** @var CommandHandler $instance */
		$instance = $this->resolver->instantiate($className);
		$template = $instance->build(new CommandTemplateBuilder());

		$this->add($template, $instance);
	}
}
