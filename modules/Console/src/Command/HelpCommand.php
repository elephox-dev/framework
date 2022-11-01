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
			->setName('help')
			->setDescription('Display a list of available commands or help for a specific command')
			->addArgument('command', true, description: 'The command to display help for')
		;
	}

	public function handle(CommandInvocation $command): ?int
	{
		$requestedCommand = $command->arguments->get('command')->value;
		if (!is_string($requestedCommand)) {
			$this->logger->info(Console::underscore('Available commands:'));

			/** @var array<int, array<int, string>> $commands */
			$commands = [];

			/**
			 * @var CommandTemplate $commandTemplate
			 */
			foreach (new FlipIterator($this->commands->getIterator()) as $commandTemplate) {
				$description = empty($commandTemplate->description) ? Console::gray('No description') : $commandTemplate->description;
				$name = Console::yellow($commandTemplate->name);

				$commands[] = [$name, $description];
			}

			foreach (Console::table($commands, compact: true, headers: [Console::yellow('Command'), 'Description']) as $line) {
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

			foreach (Console::table($metaData, noOuterBorder: true, noInnerBorder: true, noHeaders: true) as $line) {
				$this->logger->info($line);
			}

			$this->logger->info(Console::green('Arguments:'));

			$argumentData = [];
			foreach ($commandTemplate->argumentTemplates as $argumentTemplate) {
				$openBracket = $argumentTemplate->hasDefault ? '[' : '<';
				$closeBracket = $argumentTemplate->hasDefault ? ']' : '>';
				$name = $openBracket . $argumentTemplate->name;
				if ($argumentTemplate->hasDefault) {
					/** @psalm-suppress PossiblyInvalidCast */
					$name .= '=' . match (get_debug_type($argumentTemplate->default)) {
						'null' => 'null',
						'bool' => $argumentTemplate->default ? 'true' : 'false',
						'array' => implode(', ', (array) $argumentTemplate->default),
						'int', 'float', 'string', Stringable::class => (string) $argumentTemplate->default,
						default => get_debug_type($argumentTemplate->default),
					};
				}
				$name .= $closeBracket;
				$name = $argumentTemplate->hasDefault ? Console::light_gray($name) : Console::blue($name);

				$description = empty($argumentTemplate->description) ? Console::gray('No description') : $argumentTemplate->description;

				$argumentData[] = ["\t", $name, "\t", $description];
			}

			foreach (Console::table($argumentData, noOuterBorder: true, noInnerBorder: true, noHeaders: true) as $line) {
				$this->logger->info($line);
			}

			$this->logger->info(Console::green('Options:'));

			$optionData = [];
			foreach ($commandTemplate->optionTemplates as $optionTemplate) {
				$name = '[';

				if ($optionTemplate->short !== null) {
					$name .= '-' . $optionTemplate->short . '|';
				}

				$name .= '--' . $optionTemplate->name;

				if ($optionTemplate->hasValue) {
					$type = get_debug_type($optionTemplate->default);
					/** @psalm-suppress PossiblyInvalidCast */
					$name .= '=' . match ($type) {
						'null' => 'null',
						'bool' => $optionTemplate->default ? 'true' : 'false',
						'array' => implode(', ', (array) $optionTemplate->default),
						'int', 'float', 'string', Stringable::class => (string) $optionTemplate->default,
						default => $type,
					};
				}
				$name .= ']';
				$name = Console::light_gray($name);

				$description = empty($optionTemplate->description) ? Console::gray('No description') : $optionTemplate->description;

				$optionData[] = ["\t", $name, "\t", $description];
			}

			foreach (Console::table($optionData, noOuterBorder: true, noInnerBorder: true, noHeaders: true) as $line) {
				$this->logger->info($line);
			}

			return 0;
		} catch (CommandNotFoundException) {
			$this->logger->error(sprintf("Command '%s' not found", $requestedCommand));

			return 1;
		}
	}
}
