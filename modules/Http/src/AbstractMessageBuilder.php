<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\MessageBuilder;
use Elephox\Stream\Contract\Stream;
use JetBrains\PhpStorm\Pure;

abstract class AbstractMessageBuilder extends AbstractBuilder implements MessageBuilder
{
	public const DefaultProtocolVersion = '1.1';

	#[Pure]
	public function __construct(
		protected ?string $protocolVersion = null,
		protected ?Contract\HeaderMap $headers = null,
		protected ?Stream $body = null
	) {
	}

	public function protocolVersion(string $version): MessageBuilder
	{
		$this->protocolVersion = $version;

		return $this;
	}

	public function body(Stream $body): MessageBuilder
	{
		$this->body = $body;

		return $this;
	}

	public function header(string $name, array $value): MessageBuilder
	{
		if ($this->headers === null) {
			$this->headers = new HeaderMap();
		}

		$this->headers->put($name, $value);

		return $this;
	}

	public function headerMap(Contract\HeaderMap $headers): MessageBuilder
	{
		$this->headers = $headers;

		return $this;
	}
}
