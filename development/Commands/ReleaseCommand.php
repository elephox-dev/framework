<?php
declare(strict_types=1);

namespace Elephox\Development\Commands;

use Elephox\Console\Command\CommandInvocation;
use Elephox\Console\Command\CommandTemplateBuilder;
use Elephox\Console\Command\Contract\CommandHandler;

class ReleaseCommand implements CommandHandler
{
	public function configure(CommandTemplateBuilder $builder): CommandTemplateBuilder
	{
		return $builder
			->name('release')
			->description('Release a new version of the framework.')
			->argument('version', 'The version to release')
			->argument('type', 'The type of release (patch, minor, major)')
		;
	}

	public function handle(CommandInvocation $command): int|null
	{
		$version = $command->getArgument('version');
		$type = $command->getArgument('type');

		// TODO: Implement handle() method.

		return 0;
	}
}
