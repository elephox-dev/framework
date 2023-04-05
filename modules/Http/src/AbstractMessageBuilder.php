<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Files\Contract\File as FileContract;
use Elephox\Files\File;
use Elephox\Http\Contract\MessageBuilder;
use Elephox\Stream\ResourceStream;
use Elephox\Stream\StringStream;
use InvalidArgumentException;
use JetBrains\PhpStorm\Language;
use JetBrains\PhpStorm\Pure;
use JsonException;
use Psr\Http\Message\StreamInterface;

/**
 * @psalm-consistent-constructor
 */
abstract class AbstractMessageBuilder extends AbstractBuilder implements MessageBuilder
{
	public const DefaultProtocolVersion = '1.1';

	#[Pure]
	public function __construct(
		protected ?string $protocolVersion = null,
		protected ?Contract\HeaderMap $headers = null,
		protected ?StreamInterface $body = null,
	) {
	}

	public function protocolVersion(string $version): static
	{
		$this->protocolVersion = $version;

		return $this;
	}

	public function getProtocolVersion(): ?string
	{
		return $this->protocolVersion;
	}

	public function body(StreamInterface $body): static
	{
		$this->body = $body;

		return $this;
	}

	public function getBody(): ?StreamInterface
	{
		return $this->body;
	}

	public function textBody(#[Language('TEXT')] string $content): static
	{
		return $this->body(new StringStream($content));
	}

	/**
	 * @throws JsonException
	 */
	public function jsonBody(array|object $data): static
	{
		$json = json_encode($data, JSON_THROW_ON_ERROR);

		return $this->body(new StringStream($json));
	}

	public function resourceBody(mixed $resource): static
	{
		return $this->body(ResourceStream::wrap($resource));
	}

	public function htmlBody(#[Language('HTML')] string $content): static
	{
		return $this->body(new StringStream($content));
	}

	public function fileBody(string|FileContract $path): static
	{
		return $this->body(File::openStream($path));
	}

	public function header(string|HeaderName $name, string|array $value): static
	{
		if ($this->headers === null) {
			$this->headers = new HeaderMap();
		}

		$value = is_array($value) ? array_values($value) : [$value];
		if (empty($value)) {
			throw new InvalidArgumentException('Cannot set an empty array as header value. To remove headers, use with()->removedHeader()');
		}

		$this->headers->put($name, $value);

		return $this;
	}

	public function addedHeader(string|HeaderName $name, array|string $value): static
	{
		if ($this->headers === null) {
			$this->headers = new HeaderMap();
		}

		$value = is_array($value) ? array_values($value) : [$value];

		if ($this->headers->has($name)) {
			$previous = $this->headers->get($name);
		} else {
			$previous = [];
		}

		$result = array_merge($previous, $value);
		if (empty($result)) {
			throw new InvalidArgumentException('Cannot set an empty array as header value. To remove headers, use with()->removedHeader()');
		}

		$this->headers->put($name, $result);

		return $this;
	}

	public function removedHeader(string|HeaderName $name): static
	{
		if ($this->headers === null) {
			return $this;
		}

		if (!$this->headers->has($name)) {
			return $this;
		}

		$this->headers->remove($name);

		return $this;
	}

	public function headerMap(Contract\HeaderMap $headers): static
	{
		$this->headers = $headers;

		return $this;
	}

	public function getHeaderMap(): ?Contract\HeaderMap
	{
		return $this->headers;
	}
}
