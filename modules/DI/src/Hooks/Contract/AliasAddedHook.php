<?php
declare(strict_types=1);

namespace Elephox\DI\Hooks\Contract;

use Elephox\DI\Hooks\AliasHookData;

interface AliasAddedHook
{
	public function aliasAdded(AliasHookData $data): void;
}
