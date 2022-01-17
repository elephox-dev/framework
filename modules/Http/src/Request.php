<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\HeaderMap;
use Elephox\Stream\Contract\Stream;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
class Request extends AbstractMessage implements Contract\Request
{
	#[Pure]
	public function __construct(
		string $protocolVersion,
		HeaderMap $headers,
		Stream $body,
		public readonly RequestMethod $method,
		public readonly Url $url,
	) {
		parent::__construct($protocolVersion, $headers, $body);
	}

	#[Pure] public function with(): Contract\RequestBuilder
	{
		return new RequestBuilder();
	}
}
