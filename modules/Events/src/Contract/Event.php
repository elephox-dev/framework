<?php
declare(strict_types=1);

namespace Elephox\Events\Contract;

interface Event
{
	/**
	 * @return non-empty-string
	 */
	public function getName(): string;
}
