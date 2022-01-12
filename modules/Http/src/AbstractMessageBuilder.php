<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\MessageBuilder;
use Elephox\Stream\Contract\Stream;
use JetBrains\PhpStorm\Pure;
use LogicException;

abstract class AbstractMessageBuilder implements MessageBuilder
{
	public const DefaultProtocolVersion = '1.1';

	protected static function missingParameterException(string $name): LogicException
	{
		return new LogicException("Missing required parameter: $name");
	}

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
