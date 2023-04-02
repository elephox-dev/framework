<?php
declare(strict_types=1);

namespace Elephox\Development\Commands;

readonly class Version
{
	public string $name;
	public string $composerDependency;

	public function __construct(
		public int $major,
		public int $minor,
		public int $patch,
		public string $flag,
	) {
		$this->name = sprintf('%d.%d.%d%s', $this->major, $this->minor, $this->patch, $this->flag);
		$this->composerDependency = sprintf('^%d.%d', $this->major, $this->minor);
	}
}
