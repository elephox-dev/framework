<?php
declare(strict_types=1);

namespace Elephox\Host\Contract;

use Elephox\Files\Contract\Directory;

interface HostEnvironment extends Environment
{
	public function getEnvironmentName(): string;

	public function getContentRootDirectory(): Directory;
}
