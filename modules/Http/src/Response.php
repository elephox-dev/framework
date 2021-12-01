<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Support\Contract\JsonConvertible;
use Elephox\Support\Contract\MimeType as MimeTypeContract;
use Elephox\Support\MimeType;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

class Response implements Contract\Response
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
		/**
		 * @var Contract\ResponseCode|null $code
		 * @psalm-suppress UndefinedMethod Until vimeo/psalm#6429 is fixed.
		 */
		$code = ResponseCode::Tryfrom((int)$matches['code']);
		if ($code === null) {
			$trimmedMessage = trim($matches['message']);
			if (empty($trimmedMessage)) {
				throw new InvalidResponseCodeMessageException();
			}

			$code = new CustomResponseCode((int)$matches['code'], $trimmedMessage);
		}
		$headers = ResponseHeaderMap::fromString($matches['headers']);
		$body = $matches['body'];

		return new self($body, $code, $headers, $version);
	}

	public static function withJson(mixed $json = null, ResponseCode $code = ResponseCode::OK, ?Contract\ResponseHeaderMap $headers = null): Contract\Response
	{
		try {
			if ($json instanceof JsonConvertible) {
				$content = $json->toJson();
			} else if ($json !== null) {
				$content = json_encode($json, JSON_THROW_ON_ERROR);
			} else {
				$content = null;
			}

			$headers ??= new ResponseHeaderMap();
			$headers->put(HeaderName::ContentType, MimeType::Applicationjson->getValue());

			return new Response($content, $code, $headers);
		} catch (JsonException $e) {
			throw new InvalidArgumentException("Failed to encode JSON.", 0, $e);
		}
	}

	private Contract\ResponseCode $code;
	private Contract\ResponseHeaderMap $headers;

	public function __construct(private ?string $content, Contract\ResponseCode $code = ResponseCode::OK, null|Contract\ResponseHeaderMap|array $headers = null, private string $httpVersion = "1.1")
	{
		$this->code = $code;

		$this->headers = match (true) {
			$headers === null => new ResponseHeaderMap(),
			$headers instanceof Contract\ResponseHeaderMap => $headers,
			is_array($headers) => ResponseHeaderMap::fromArray($headers),
		};

		if ($this->headers->anyKey(static fn(Contract\HeaderName $name) => $name->isOnlyRequest())) {
			throw new InvalidArgumentException("Responses cannot contain headers reserved for requests only.");
		}
	}

	public function getHeaders(): Contract\ResponseHeaderMap
	{
		return $this->headers;
	}

	public function setCode(Contract\ResponseCode $code): void
	{
		$this->code = $code;
	}

	public function getCode(): Contract\ResponseCode
	{
		return $this->code;
	}

	public function setContent(?string $content, ?MimeTypeContract $mimeType = null): void
	{
		$this->content = $content;

		if ($mimeType !== null) {
			$this->headers->put(HeaderName::ContentType, $mimeType->getValue());
		}
	}

	public function getContent(): ?string
	{
		return $this->content;
	}

	public function getHttpVersion(): string
	{
		return $this->httpVersion;
	}

	public function send(): void
	{
		if (headers_sent($filename, $line)) {
			throw new RuntimeException("Headers already sent in $filename:$line");
		}

		http_response_code($this->code->getCode());
		$headers = $this->getHeaders()->asArray();
		foreach ($headers as $header => $value) {
			if (is_array($value)) {
				foreach ($value as $v) {
					header("$header: $v", false);
				}
			} else {
				header("$header: $value");
			}
		}

		if (!array_key_exists("X-Powered-By", $headers) && defined("ELEPHOX_VERSION") && ini_get("expose_php")) {
			header("X-Powered-By: Elephox/" . ELEPHOX_VERSION . " PHP/" . PHP_VERSION);
		}

		if (!empty($this->content)) {
			echo $this->content;
		}
	}
}
