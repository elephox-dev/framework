<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Files\Contract\File as FileContract;
use Elephox\Files\File;
use Elephox\Http\Contract\MessageBuilder;
use Elephox\Stream\Contract\Stream;
use Elephox\Stream\ResourceStream;
use Elephox\Stream\StringStream;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use JsonException;

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
		protected ?Stream $body = null,
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

	public function body(Stream $body): static
	{
		$this->body = $body;

		return $this;
	}

	public function getBody(): ?Stream
	{
		return $this->body;
	}

	/**
	 * @throws JsonException
	 *
	 * @param array $data
	 */
	public function jsonBody(array $data): static
	{
		$json = json_encode($data, JSON_THROW_ON_ERROR);

		return $this->body(new StringStream($json));
	}

	public function resourceBody(mixed $resource): static
	{
		if (!is_resource($resource)) {
			throw new InvalidArgumentException('$resource must be a resource');
		}

		return $this->body(new ResourceStream($resource));
	}

	public function htmlBody(string $content): static
	{
		return $this->body(new StringStream($content));
	}

	public function fileBody(string|FileContract $path): static
	{
		return $this->body(File::openStream($path));
	}

	public function header(string $name, string|array $value): static
	{
		if ($this->headers === null) {
			$this->headers = new HeaderMap();
		}

		$this->headers->put($name, is_array($value) ? $value : [$value]);

		return $this;
	}

	public function addHeader(string $name, array|string $value): static
	{
		if ($this->headers === null) {
			$this->headers = new HeaderMap();
		}

		$value = is_array($value) ? $value : [$value];

		if ($this->headers->has($name)) {
			$previous = $this->headers->get($name);

			$previous = is_array($previous) ? $previous : [$previous];
		} else {
			$previous = [];
		}

		$this->headers->put($name, array_merge($previous, $value));

		return $this;
	}

	public function removeHeader(string $name): static
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
