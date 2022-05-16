<?php
declare(strict_types=1);

namespace Elephox\Development\Commands;

use Elephox\Console\Command\CommandInvocation;
use Elephox\Console\Command\CommandTemplateBuilder;
use Elephox\Console\Command\Contract\CommandHandler;
use function ctype_digit;

class EchoCommand implements CommandHandler
{
	public function configure(CommandTemplateBuilder $builder): void
	{
		$builder->setName('echo');
		$builder->setDescription('Echo a message');
		$builder->addArgument('message', description: 'The message to echo');
		$builder->addOption('repeat', 'r', '1', 'Repeat the message', static fn (mixed $v) => ctype_digit((string) $v));
	}

	public function handle(CommandInvocation $command): int|null
	{
		for ($i = 0; $i < $command->options->get('repeat')->value; $i++) {
			/** @psalm-suppress PossiblyNullOperand */
			echo $command->arguments->get('message')->value . PHP_EOL;
		}

		return 0;
	}
}
