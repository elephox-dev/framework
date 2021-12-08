<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Support\Contract\MimeType as MimeTypeContract;
use Elephox\Support\MimeType;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class Response extends AbstractHttpMessage implements Contract\Response
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

	final public function __construct(
		private Contract\ResponseCode $code = ResponseCode::OK,
		?Contract\ResponseHeaderMap $headers = null,
		?StreamInterface $body = null,
		string $protocolVersion = "1.1"
	) {
		parent::__construct($headers, $body, $protocolVersion);

		if ($this->headers->anyKey(static fn(Contract\HeaderName $name) => $name->isOnlyRequest())) {
			throw new InvalidArgumentException("Responses cannot contain headers reserved for requests only.");
		}
	}

	public function getResponseCode(): Contract\ResponseCode
	{
		return $this->code;
	}

	public function getHeaderMap(): Contract\ResponseHeaderMap
	{
		return $this->headers->asResponseHeaders();
	}

	public function withoutBody(): static
	{
		return new static(clone $this->code, (clone $this->headers)->asResponseHeaders(), new EmptyStream(), $this->protocolVersion);
	}

	public function withProtocolVersion($version): static
	{
		return new static(clone $this->code, (clone $this->headers)->asResponseHeaders(), clone $this->body, $version);
	}

	public function withHeader($name, $value): static
	{
		$headerName = HeaderMap::parseHeaderName($name);

		return $this->withHeaderName($headerName, $value);
	}

	public function withAddedHeader($name, $value): static
	{
		$headerName = HeaderMap::parseHeaderName($name);

		return $this->withHeaderName($headerName, $value);
	}

	public function withoutHeader($name): static
	{
		$headerName = HeaderMap::parseHeaderName($name);

		return $this->withoutHeaderName($headerName);
	}

	public function withBody(StreamInterface $body): static
	{
		return new static(clone $this->code, (clone $this->headers)->asResponseHeaders(), $body, $this->protocolVersion);
	}

	public function withResponseCode(Contract\ResponseCode $code): static
	{
		return new static($code, (clone $this->headers)->asResponseHeaders(), clone $this->body, $this->protocolVersion);
	}

	public function getStatusCode(): int
	{
		return $this->code->getCode();
	}

	public function getReasonPhrase(): string
	{
		return $this->code->getMessage();
	}

	public function withStatus($code, $reasonPhrase = ''): static
	{
		$responseCode = ResponseCode::tryfrom($code);
		if ($responseCode === null) {
			if (empty($reasonPhrase)) {
				throw new InvalidArgumentException("Reason phrase cannot be empty for custom response codes.");
			}

			$responseCode = new CustomResponseCode($code, $reasonPhrase);
		}

		return $this->withResponseCode($responseCode);
	}

	public function getMimeType(): ?MimeTypeContract
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

	public function withMimeType(?MimeTypeContract $mimeType): static
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
		// TODO: Implement send() method.
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
