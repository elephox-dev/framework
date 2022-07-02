<?php
declare(strict_types=1);

namespace Elephox\DI\Hooks\Contract;

use Elephox\DI\Hooks\AliasHookData;

interface UnknownAliasRequestedHook
{
	public function unknownAliasRequested(AliasHookData $data): void;
}
