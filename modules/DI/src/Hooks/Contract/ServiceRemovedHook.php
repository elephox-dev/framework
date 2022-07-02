<?php
declare(strict_types=1);

namespace Elephox\DI\Hooks\Contract;

use Elephox\DI\Hooks\ServiceDescriptorHookData;

interface ServiceRemovedHook
{
	/**
	 * @template TService of object
	 *
	 * @param ServiceDescriptorHookData<TService> $data
	 *
	 * @return void
	 */
	public function serviceRemoved(ServiceDescriptorHookData $data): void;
}
