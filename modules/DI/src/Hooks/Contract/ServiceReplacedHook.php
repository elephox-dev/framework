<?php
declare(strict_types=1);

namespace Elephox\DI\Hooks\Contract;

use Elephox\DI\Hooks\ServiceReplacedHookData;

interface ServiceReplacedHook
{
	public function serviceReplaced(ServiceReplacedHookData $data): void;
}
