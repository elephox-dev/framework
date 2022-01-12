<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\HeaderMap;
use Elephox\Http\Contract\Message;
use Elephox\Stream\Contract\Stream;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
abstract class AbstractMessage implements Message
{
	#[Pure]
	public function __construct(
		public readonly string $protocolVersion,
		public readonly HeaderMap $headers,
		public readonly Stream $body,
	) {
	}
}
