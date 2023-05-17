<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

readonly class CommandInvocation
{
	public function __construct(
		public RawCommandInvocation $raw,
		public ArgumentList $arguments,
		public OptionList $options,
	) {
	}
}
