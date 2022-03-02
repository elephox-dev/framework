<?php

namespace Elephox\Core\Contract;

use ArrayAccess;

interface Environment extends ArrayAccess
{
	public function isDebug(): bool;
}
