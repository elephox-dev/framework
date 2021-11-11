<?php
declare(strict_types=1);

namespace Philly\Http;

use InvalidArgumentException;
use JsonException;

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

		if ($result === 0) {
			throw new InvalidArgumentException("Invalid response string given.");
		}

		if ($result === false) {
			throw new InvalidArgumentException("Error parsing response string: " . preg_last_error());
		}

		$version = $matches['version'];
		/**
		 * @var Contract\ResponseCode $code
		 * @psalm-suppress UndefinedMethod Until vimeo/psalm#6429 is fixed.
		 */
		$code = ResponseCode::from((int)$matches['code']);
		$headers = ResponseHeaderMap::fromString($matches['headers']);
		$body = $matches['body'];

		return new self($body, $code, $headers, $version);
	}

	/**
	 * @throws JsonException
	 */
	public static function withJson(mixed $json, ResponseCode $code = ResponseCode::Ok, ?Contract\ResponseHeaderMap $headers = null): Contract\Response
	{
		$content = json_encode($json, JSON_THROW_ON_ERROR);

		return new Response($content, $code, $headers);
	}

	private Contract\ResponseCode $code;
	private Contract\ResponseHeaderMap $headers;

	public function __construct(private string $content, Contract\ResponseCode $code = ResponseCode::Ok, ?Contract\ResponseHeaderMap $headers = null, private string $httpVersion = "1.1")
	{
		$this->code = $code;
		$this->headers = $headers ?? new ResponseHeaderMap();
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

	public function setContent(string $content): void
	{
		$this->content = $content;
	}

	public function getContent(): string
	{
		return $this->content;
	}

	public function getHttpVersion(): string
	{
		return $this->httpVersion;
	}
}
