<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Stream\Contract\Stream;

interface MessageBuilder
{
	public function protocolVersion(string $version): MessageBuilder;

	public function body(Stream $body): MessageBuilder;

	public function header(string $name, array $value): MessageBuilder;

	public function headerMap(HeaderMap $headers): MessageBuilder;

	public function build(): Message;
}
