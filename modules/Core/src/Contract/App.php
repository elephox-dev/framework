<?php
declare(strict_types=1);

namespace Elephox\Core\Contract;

interface App
{
	/**
	 * @template TServiceContainer of ServiceContainer
	 *
	 * @return AppBuilder<self, TServiceContainer>
	 */
	public static function createBuilder(): AppBuilder;
}
