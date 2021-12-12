<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\Stream;
use Elephox\Support\Contract\MimeType as MimeTypeContract;
use Elephox\Support\MimeType;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use RuntimeException;

/**
 * @psalm-consistent-constructor
 */
class Response extends AbstractMessage implements Contract\Response
{
	public const Pattern = '/HTTP\/(?<version>\S+)\s(?<code>\S+)\s(?<message>[^\r\n]+)\r?\n(?<headers>(?:(?:[^:]+):\s*(?:[^\r\n]+)\r?\n)*)\r?\n(?<body>.*)/s';

	protected static function createHeaderMap(): Contract\ResponseHeaderMap
	{
		return new ResponseHeaderMap();
	}

	public static function fromString(string $responseString): self
	{
		$result = preg_match(
			self::Pattern,
			$responseString,
			$matches
		);

		if (!$result) {
			throw new InvalidArgumentException("Invalid response string given.");
		}

		$version = $matches['version'];
		$code = ResponseCode::tryfrom((int)$matches['code']);
		if ($code === null) {
			$trimmedMessage = trim($matches['message']);
			if (empty($trimmedMessage)) {
				throw new InvalidResponseCodeMessageException();
			}

			$code = new CustomResponseCode((int)$matches['code'], $trimmedMessage);
		}
		$headers = ResponseHeaderMap::fromString($matches['headers']);
		$body = new StringStream($matches['body']);

		return new self($code, $headers, $body, $version);
	}

	public function __construct(
		private Contract\ResponseCode $code = ResponseCode::OK,
		?Contract\ResponseHeaderMap $headers = null,
		?Stream $body = null,
		string $protocolVersion = "1.1"
	) {
		parent::__construct($headers, $body, $protocolVersion);

		if ($this->headers->anyKey(static fn(Contract\HeaderName $name) => $name->isOnlyRequest())) {
			throw new InvalidArgumentException("Responses cannot contain headers reserved for requests only.");
		}
	}

	#[Pure] public function getResponseCode(): Contract\ResponseCode
	{
		return $this->code;
	}

	#[Pure] public function getHeaderMap(): Contract\ResponseHeaderMap
	{
		return $this->headers->asResponseHeaders();
	}

	public function withoutBody(): static
	{
		return new static(clone $this->code, (clone $this->headers)->asResponseHeaders(), new EmptyStream(), $this->protocolVersion);
	}

	public function withProtocolVersion(string $version): static
	{
		return new static(clone $this->code, (clone $this->headers)->asResponseHeaders(), clone $this->body, $version);
	}

	public function withBody(Stream $body): static
	{
		return new static(clone $this->code, (clone $this->headers)->asResponseHeaders(), $body, $this->protocolVersion);
	}

	public function withResponseCode(Contract\ResponseCode $code): static
	{
		return new static($code, (clone $this->headers)->asResponseHeaders(), clone $this->body, $this->protocolVersion);
	}

	#[Pure] public function getContentType(): ?MimeTypeContract
	{
		if (!$this->headers->has(HeaderName::ContentType)) {
			return null;
		}

		$value = $this->headers->get(HeaderName::ContentType)->first();
		if ($value === null) {
			return null;
		}

		return MimeType::tryfrom($value);
	}

	public function withContentType(?MimeTypeContract $mimeType): static
	{
		if ($mimeType === null) {
			return $this->withoutHeaderName(HeaderName::ContentType);
		}

		return $this->withHeaderName(HeaderName::ContentType, $mimeType->getValue());
	}

	public function withHeaderMap(Contract\HeaderMap $map): static
	{
		return new static(clone $this->code, $map->asResponseHeaders(), clone $this->body, $this->protocolVersion);
	}

	public function send(): void
	{
		if (headers_sent($filename, $line)) {
			throw new RuntimeException("Headers already sent in $filename:$line");
		}

		http_response_code($this->code->getCode());
		$headers = $this->getHeaderMap()->asArray();
		foreach ($headers as $header => $value) {
			foreach ($value as $v) {
				header("$header: $v", false);
			}
		}

		if (!array_key_exists("X-Powered-By", $headers) && defined("ELEPHOX_VERSION") && ini_get("expose_php")) {
			header("X-Powered-By: Elephox/" . ELEPHOX_VERSION . " PHP/" . PHP_VERSION);
		}

		echo $this->getBody()->getContents();
	}
}
