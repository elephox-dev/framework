<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use InvalidArgumentException;
use LogicException;

/**
 * @property string $name
 * @property string $invokedBinary
 * @property string $commandLine
 */
class CommandInvocation
{
	public function __construct(
		public readonly RawCommandInvocation $raw,
		public readonly ArgumentList $arguments,
		public readonly OptionList $options,
	) {
	}
}
