<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Mimey\MimeTypeInterface;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

#[Immutable]
interface Message extends MessageInterface
{
	#[Pure]
	public static function build(): MessageBuilder;

	#[Pure]
	public function with(): MessageBuilder;

	#[Pure]
	public function getProtocolVersion(): string;

	#[Pure]
	public function getHeaderMap(): HeaderMap;

	#[Pure]
	public function getContentType(): ?MimeTypeInterface;

	#[Pure]
	public function getBody(): StreamInterface;
}
