<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Autoloading\Composer\NamespaceLoader;
use Elephox\Collection\ObjectMap;
use Elephox\Console\Command\Contract\CommandHandler;
use Elephox\DI\Contract\Resolver;
use Iterator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<CommandTemplate, CommandHandler>
 */
class CommandCollection implements IteratorAggregate
{
	/**
	 * @var ObjectMap<CommandTemplate, CommandHandler> $templateMap
	 */
	private readonly ObjectMap $templateMap;

	public function __construct(private readonly Resolver $resolver)
	{
		$this->templateMap = new ObjectMap();
	}

	public function add(CommandTemplate $template, CommandHandler $handler): void
	{
		$this->templateMap->put($template, $handler);
	}

	public function loadFromNamespace(string $namespace): static
	{
		NamespaceLoader::iterateNamespace($namespace, function (string $className): void {
			$this->loadFromClass($className);
		});

		return $this;
	}

	protected function preProcessCommandTemplate(CommandTemplateBuilder $builder): void
	{
		// Do nothing by default
	}

	protected function postProcessCommandTemplate(CommandTemplateBuilder $builder): void
	{
		// Do nothing by default
	}

	/**
	 * @param class-string $className
	 */
	public function loadFromClass(string $className): void
	{
		$interfaces = class_implements($className);
		if (!$interfaces || !in_array(CommandHandler::class, $interfaces, true)) {
			return;
		}

		/** @var CommandHandler $instance */
		$instance = $this->resolver->instantiate($className);

		$templateBuilder = new CommandTemplateBuilder();
		$this->preProcessCommandTemplate($templateBuilder);
		$instance->configure($templateBuilder);
		$this->postProcessCommandTemplate($templateBuilder);
		$template = $templateBuilder->build();

		$this->add($template, $instance);
	}

	public function findCompiled(RawCommandInvocation $invocation): CompiledCommandHandler
	{
		return $this->templateMap
			->whereKey(static fn (CommandTemplate $template): bool => $template->name === $invocation->name)
			->select(static fn (CommandHandler $handler, CommandTemplate $template): CompiledCommandHandler => new CompiledCommandHandler($invocation, $template, $handler))
			->firstOrDefault(null)
			?? throw new CommandNotFoundException($invocation->name);
	}

	public function getTemplateByName(string $name): CommandTemplate
	{
		return $this->templateMap
			->flip()
			->where(static fn (CommandTemplate $template): bool => $template->name === $name)
			->firstOrDefault(null)
		?? throw new CommandNotFoundException($name);
	}

	public function getIterator(): Iterator
	{
		return $this->templateMap->getIterator();
	}
}
