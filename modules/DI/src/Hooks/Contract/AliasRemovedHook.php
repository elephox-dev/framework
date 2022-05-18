<?php
declare(strict_types=1);

namespace Elephox\DI\Hooks\Contract;

use Elephox\DI\Hooks\AliasHookData;

interface AliasRemovedHook
{
	public function aliasRemoved(AliasHookData $data): void;
}
