<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Collection\Iterator\FlipIterator;
use Elephox\Logging\Contract\Logger;
use ricardoboss\Console;

class HelpCommand implements Contract\CommandHandler
{
	public function __construct(
		private readonly CommandCollection $commands,
		private readonly Logger $logger,
	)
	{
	}

	public function configure(CommandTemplateBuilder $builder): void
	{
		$builder
			->name('help')
			->description('Display help for a command')
			->argument('command', 'The command to display help for', required: false)
		;
	}

	public function handle(CommandInvocation $command): int|null
	{
		$requestedCommand = $command->getOptionalArgument('command')?->value;
		if (!is_string($requestedCommand)) {
			$this->logger->info(Console::underscore('Available commands:'));

			/**
			 * @var CommandTemplate $commandTemplate
			 */
			foreach (new FlipIterator($this->commands->getIterator()) as $commandTemplate) {
				$this->logger->info(sprintf("\t%s\t\t%s", Console::yellow($commandTemplate->name), empty($commandTemplate->description) ? Console::gray('No description') : $commandTemplate->description));
			}

			return 0;
		}

		try {
			$commandTemplate = $this->commands->getTemplateByName($requestedCommand);

			$this->logger->info(Console::underscore(sprintf("Help for command '%s':", Console::yellow($commandTemplate->name))));
			$this->logger->info(sprintf("\t%s\t\t\t%s", Console::green('Name'), $commandTemplate->name));
			$this->logger->info(sprintf("\t%s\t\t%s", Console::green('Description'), empty($commandTemplate->description) ? Console::gray('No description') : $commandTemplate->description));
			$this->logger->info(sprintf("\t%s\t\t%s", Console::green('Arguments'), empty($commandTemplate->argumentTemplates) ? Console::gray('No arguments') : implode(' ', $commandTemplate->argumentTemplates->select(fn (ArgumentTemplate $t) => ($t->required ? '<' : '[') . $t->name . ($t->required ? '>' : ']'))->toList())));

			return 0;
		} catch (CommandNotFoundException) {
			$this->logger->error(sprintf("Command '%s' not found", $requestedCommand));

			return 1;
		}
	}
}
