<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\Response as ResponseContract;
use Elephox\Http\Contract\ResponseBuilder as ResponseBuilderContract;
use RuntimeException;

class ResponseSender
{
	public static function sendResponse(ResponseContract|ResponseBuilderContract $response): void
	{
		if ($response instanceof ResponseBuilderContract) {
			$response = $response->get();
		}

		self::sendHeaders($response);
		self::sendBody($response);
	}

	public static function sendHeaders(ResponseContract|ResponseBuilderContract $response, bool $throwIfSent = false): void
	{
		if ($response instanceof ResponseBuilderContract) {
			$response = $response->get();
		}

		if (headers_sent()) {
			if ($throwIfSent) {
				throw new RuntimeException('Headers already sent');
			}

			return;
		}

		http_response_code($response->getResponseCode()->value);

		$contentTypeSent = false;
		foreach ($response->getHeaderMap() as $headerName => $values) {
			if (is_array($values)) {
				header("$headerName: " . implode(",", $values));
			} else {
				header("$headerName: $values");
			}

			if ($headerName === 'Content-Type') {
				$contentTypeSent = true;
			}
		}

		if (!$contentTypeSent && $response->getMimeType() !== null) {
			header('Content-Type: ' . $response->getMimeType()->getValue());
		}
	}

	public static function sendBody(ResponseContract|ResponseBuilderContract $response): void
	{
		if ($response instanceof ResponseBuilderContract) {
			$response = $response->get();
		}

		echo $response->getBody()->getContents();
	}
}
