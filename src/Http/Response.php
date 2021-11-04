<?php

namespace Philly\Http;

use JsonException;

class Response implements Contract\Response
{
	/**
	 * @throws JsonException
	 */
	public static function fromJson(mixed $json, ResponseCode $code = ResponseCode::Ok, ?Contract\HeaderMap $headers = null): Contract\Response
	{
		$content = json_encode($json, JSON_THROW_ON_ERROR);

		return new Response($content, $code, $headers);
	}

	private Contract\HeaderMap $headers;

	private ResponseCode $code;

	private string $content;

	public function __construct(string $content, ResponseCode $code = ResponseCode::Ok, ?Contract\HeaderMap $headers = null)
	{
		$this->content = $content;
		$this->code = $code;
		$this->headers = $headers ?? new HeaderMap();
	}

	public function getHeaders(): Contract\HeaderMap
	{
		return $this->headers;
	}

	public function setCode(ResponseCode $code): void
	{
		$this->code = $code;
	}

	public function getCode(): ResponseCode
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
}
