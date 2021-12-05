<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Support\Contract\MimeType as MimeTypeContract;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

class Response extends AbstractHttpMessage implements Contract\Response
{
	public const Pattern = '/HTTP\/(?<version>\S+)\s(?<code>\S+)\s(?<message>[^\r\n]+)\r?\n(?<headers>(?:(?:[^:]+):\s*(?:[^\r\n]+)\r?\n)*)\r?\n(?<body>.*)/s';

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

		return new self($body, $version, $code, $headers);
	}

	public function __construct(StreamInterface $body, string $protocolVersion, private Contract\ResponseCode $code, Contract\ResponseHeaderMap $headers)
	{
		parent::__construct($protocolVersion, $headers, $body);

		if ($this->headers->anyKey(static fn(Contract\HeaderName $name) => $name->isOnlyRequest())) {
			throw new InvalidArgumentException("Responses cannot contain headers reserved for requests only.");
		}
	}

	public function getResponseCode(): Contract\ResponseCode
	{
		return $this->code;
	}

	public function send(): void
	{
		// TODO: Implement send() method.
//		if (headers_sent($filename, $line)) {
//			throw new RuntimeException("Headers already sent in $filename:$line");
//		}
//
//		http_response_code($this->code->getCode());
//		$headers = $this->getHeaderMap()->asArray();
//		foreach ($headers as $header => $value) {
//			if (is_array($value)) {
//				foreach ($value as $v) {
//					header("$header: $v", false);
//				}
//			} else {
//				header("$header: $value");
//			}
//		}
//
//		if (!array_key_exists("X-Powered-By", $headers) && defined("ELEPHOX_VERSION") && ini_get("expose_php")) {
//			header("X-Powered-By: Elephox/" . ELEPHOX_VERSION . " PHP/" . PHP_VERSION);
//		}
//
//		if (!empty($this->content)) {
//			echo $this->content;
//		}
	}

	public function getHeaderMap(): Contract\ResponseHeaderMap
	{
		// TODO: Implement getHeaderMap() method.
	}

	public function withoutBody(): static
	{
		// TODO: Implement withoutBody() method.
	}

	public function withProtocolVersion($version): static
	{
		// TODO: Implement withProtocolVersion() method.
	}

	public function withHeader($name, $value): static
	{
		// TODO: Implement withHeader() method.
	}

	public function withAddedHeader($name, $value): static
	{
		// TODO: Implement withAddedHeader() method.
	}

	public function withoutHeader($name): static
	{
		// TODO: Implement withoutHeader() method.
	}

	public function withBody(StreamInterface $body): static
	{
		// TODO: Implement withBody() method.
	}

	public function withResponseCode(Contract\ResponseCode $code): Contract\Response
	{
		// TODO: Implement withResponseCode() method.
	}

	public function getMimeType(): ?MimeTypeContract
	{
		// TODO: Implement getMimeType() method.
	}

	public function withMimeType(?MimeTypeContract $mimeType): Contract\Response
	{
		// TODO: Implement withMimeType() method.
	}

	public function getStatusCode()
	{
		// TODO: Implement getStatusCode() method.
	}

	public function withStatus($code, $reasonPhrase = '')
	{
		// TODO: Implement withStatus() method.
	}

	public function getReasonPhrase()
	{
		// TODO: Implement getReasonPhrase() method.
	}
}
