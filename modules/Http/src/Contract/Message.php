<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\GenericList;
use Elephox\Stream\Contract\Stream;
use JetBrains\PhpStorm\Pure;

interface Message
{
	public function getProtocolVersion(): string;

	public function getBody(): Stream;

	public function getHeaderMap(): HeaderMap;

	public function with(): MessageBuilder;
}
