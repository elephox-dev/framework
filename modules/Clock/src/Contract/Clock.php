<?php
declare(strict_types=1);

namespace Elephox\Clock\Contract;

use JetBrains\PhpStorm\Pure;
use Psr\Clock\ClockInterface;

interface Clock extends ClockInterface
{
	#[Pure]
	public function diff(ClockInterface $clock): Duration;

	#[Pure]
	public function add(Duration $duration): self;

	#[Pure]
	public function sub(Duration $duration): self;

	#[Pure]
	public function equals(ClockInterface $clock): bool;

	#[Pure]
	public function compare(ClockInterface $clock): int;
}
