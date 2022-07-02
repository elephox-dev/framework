<?php
declare(strict_types=1);

namespace Elephox\DI\Hooks;

class AliasHookData
{
	/**
	 * @param non-empty-string $alias
	 * @param class-string|null $serviceName
	 */
	public function __construct(
		public readonly string $alias,
		public ?string $serviceName,
	) {
	}
}
