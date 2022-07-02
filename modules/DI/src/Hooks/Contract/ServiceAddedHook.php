<?php
declare(strict_types=1);

namespace Elephox\DI\Hooks\Contract;

use Elephox\DI\Hooks\ServiceDescriptorHookData;

interface ServiceAddedHook
{
	/**
	 * @template TService of object
	 *
	 * @param ServiceDescriptorHookData<TService> $data
	 *
	 * @return void
	 */
	public function serviceAdded(ServiceDescriptorHookData $data): void;
}
