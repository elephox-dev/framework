<?php
declare(strict_types=1);

namespace Elephox\Console\Command\Contract;

use Elephox\Console\Command\Command;
use Elephox\Console\Command\CommandTemplateBuilder;

interface CommandHandler
{
	public function build(CommandTemplateBuilder $builder): CommandTemplateBuilder;

	public function handle(Command $command): void;
}
