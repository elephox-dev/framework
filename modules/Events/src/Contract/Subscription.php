<?php
declare(strict_types=1);

namespace Elephox\Events\Contract;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
interface Subscription
{
	/**
	 * @return non-empty-string
	 */
	#[Pure] public function getEventName(): string;

	/**
	 * @return non-empty-string
	 */
	#[Pure] public function getId(): string;

	/**
	 * @return callable(Event): void
	 */
	#[Pure] public function getCallback(): callable;
}
