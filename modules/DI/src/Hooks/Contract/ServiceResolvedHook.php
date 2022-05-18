<?php
declare(strict_types=1);

namespace Elephox\DI\Hooks\Contract;

use Elephox\DI\Hooks\ServiceResolvedHookData;

interface ServiceResolvedHook
{
	/**
	 * @template TService of object
	 *
	 * @param ServiceResolvedHookData<TService> $data
	 *
	 * @return void
	 */
	public function serviceResolved(ServiceResolvedHookData $data): void;
}
