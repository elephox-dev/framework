<?php
declare(strict_types=1);

namespace Elephox\Development\Commands;

class Version
{
	public readonly string $name;
	public readonly string $composerDependency;

	public function __construct(
		public readonly int $major,
		public readonly int $minor,
		public readonly int $patch,
		public readonly string $flag,
	) {
		$this->name = sprintf('%d.%d.%d%s', $this->major, $this->minor, $this->patch, $this->flag);
		$this->composerDependency = sprintf('^%d.%d', $this->major, $this->minor);
	}
}
