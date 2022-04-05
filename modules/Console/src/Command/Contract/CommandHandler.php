<?php
declare(strict_types=1);

namespace Elephox\Console\Command\Contract;

use Elephox\Console\Command\CommandInvocation;
use Elephox\Console\Command\CommandTemplateBuilder;

interface CommandHandler
{
	public function configure(CommandTemplateBuilder $builder): void;

	public function handle(CommandInvocation $command): int|null;
}
