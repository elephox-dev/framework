<?php
declare(strict_types=1);

namespace Elephox\Host\Contract;

use ArrayAccess;
use Elephox\DI\Contract\NotContainerSerializable;

interface Environment extends ArrayAccess, NotContainerSerializable
{
	public function isDebug(): bool;
}
