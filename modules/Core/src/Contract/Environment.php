<?php

namespace Elephox\Core\Contract;

use ArrayAccess;
use Elephox\DI\Contract\NotContainerSerializable;

interface Environment extends ArrayAccess, NotContainerSerializable
{
	public function isDebug(): bool;
}
