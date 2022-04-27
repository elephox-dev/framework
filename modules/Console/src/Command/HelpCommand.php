<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Collection\Iterator\FlipIterator;
use Psr\Log\LoggerInterface;
use ricardoboss\Console;
use Stringable;

class HelpCommand implements Contract\CommandHandler
{
	public function __construct(
		private readonly CommandCollection $commands,
		private readonly LoggerInterface $logger,
	) {
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

			/** @var array<int, array<int, string>> $commands */
			$commands = [
				[Console::yellow('Command'), 'Description'],
			];

			/**
			 * @var CommandTemplate $commandTemplate
			 */
			foreach (new FlipIterator($this->commands->getIterator()) as $commandTemplate) {
				$description = empty($commandTemplate->description) ? Console::gray('No description') : $commandTemplate->description;
				$name = Console::yellow($commandTemplate->name);

				$commands[] = [$name, $description];
			}

			foreach (Console::table($commands, compact: true) as $line) {
				$this->logger->info($line);
			}

			return 0;
		}

		try {
			$commandTemplate = $this->commands->getTemplateByName($requestedCommand);

			$this->logger->info(Console::underscore(sprintf("Help for command '%s':", $commandTemplate->name)));

			$metaData = [
				[Console::green('Name'), "\t", $commandTemplate->name],
				[Console::green('Description'), "\t", (empty($commandTemplate->description) ? Console::gray('No description') : $commandTemplate->description)],
			];

			foreach (Console::table($metaData, noBorder: true) as $line) {
				$this->logger->info($line);
			}

			$this->logger->info(Console::green('Arguments:'));

			$argumentData = [];
			foreach ($commandTemplate->argumentTemplates as $argumentTemplate) {
				$openBracket = $argumentTemplate->required ? '<' : '[';
				$closeBracket = $argumentTemplate->required ? '>' : ']';
				$name = $openBracket . $argumentTemplate->name;
				if (!$argumentTemplate->required) {
					$name .= '=' . match (get_debug_type($argumentTemplate->default)) {
						'null' => 'null',
						'bool' => $argumentTemplate->default ? 'true' : 'false',
						'int', 'float', 'string', Stringable::class => (string) $argumentTemplate->default,
						default => get_debug_type($argumentTemplate->default),
					};
				}
				$name .= $closeBracket;
				$name = $argumentTemplate->required ? Console::blue($name) : Console::light_gray($name);

				$description = empty($argumentTemplate->description) ? Console::gray('No description') : $argumentTemplate->description;

				$argumentData[] = ["\t", $name, "\t", $description];
			}

			foreach (Console::table($argumentData, noBorder: true) as $line) {
				$this->logger->info($line);
			}

			return 0;
		} catch (CommandNotFoundException) {
			$this->logger->error(sprintf("Command '%s' not found", $requestedCommand));

			return 1;
		}
	}
}
