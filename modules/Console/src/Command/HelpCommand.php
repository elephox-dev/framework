<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\DI\Contract\ServiceProvider;
use Psr\Log\LoggerInterface;
use ricardoboss\Console;
use Stringable;

readonly class HelpCommand implements Contract\CommandHandler
{
	public function __construct(
		private ServiceProvider $services,
		private LoggerInterface $logger,
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
		$commandProvider = $this->services->require(CommandProvider::class);

		$requestedCommand = $command->arguments->get('command')->nullableString();
		if (!is_string($requestedCommand)) {
			return $this->showAllCommands($commandProvider);
		}

		try {
			$commandMetadata = $commandProvider->get($requestedCommand);

			return $this->handleRequestedCommand($commandMetadata);
		} catch (CommandNotFoundException) {
			$this->logger->error(sprintf("Command '%s' not found", $requestedCommand));

			return 1;
		}
	}

	protected function showAllCommands(CommandProvider $commandProvider): int
	{
		$this->logger->info(Console::underscore('Available commands:'));

		/** @var list<list<string>> $commands */
		$commands = [];

		/** @var CommandMetadata $commandMetadata */
		foreach ($commandProvider as $commandMetadata) {
			$template = $commandMetadata->template;
			$description = empty($template->description) ? Console::gray('No description') : $template->description;
			$name = Console::yellow($template->name);

			$commands[] = [$name, $description];
		}

		foreach (Console::table($commands, compact: true, headers: [Console::yellow('Command'), 'Description']) as $line) {
			$this->logger->info($line);
		}

		return 0;
	}

	protected function handleRequestedCommand(CommandMetadata $metadata): int
	{
		$template = $metadata->template;

		$this->logger->info(Console::underscore(sprintf("Help for command '%s':", $template->name)));

		$metaData = [
			[Console::green('Name'), "\t", $template->name],
			[Console::green('Description'), "\t", (empty($template->description) ? Console::gray('No description') : $template->description)],
		];

		foreach (Console::table($metaData, noOuterBorder: true, noInnerBorder: true, noHeaders: true) as $line) {
			$this->logger->info($line);
		}

		$this->logger->info(Console::green('Arguments:'));

		$argumentData = [];
		foreach ($template->argumentTemplates as $argumentTemplate) {
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
		foreach ($template->optionTemplates as $optionTemplate) {
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
	}
}
