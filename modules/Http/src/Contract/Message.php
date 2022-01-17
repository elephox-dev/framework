<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Stream\Contract\Stream;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
interface Message
{
	#[Pure]
	public function with(): MessageBuilder;

	#[Pure]
	public function getProtocolVersion(): string;

	#[Pure]
	public function getHeaderMap(): HeaderMap;

	#[Pure]
	public function getBody(): Stream;
}
