<?php
declare(strict_types=1);

namespace Elephox\Logging\Vendors\Logtail;

use LogicException;
use CurlHandle;
use RuntimeException;
use function curl_init;
use function curl_setopt;
use function extension_loaded;

if (!extension_loaded('curl')) {
	throw new LogicException('The curl extension is needed to use the LogtailClient');
}

/**
 * Format JSON records for Logtail
 *
 * Forked from the original LogtailClient.php in the logtail/monolog-logtail project.
 *
 * @copyright Better Stack, 2022 Logtail
 *
 * @see https://github.com/logtail/monolog-logtail/blob/d1c5675e0ca6ed7b37de70e70eb305713cee034d/src/Monolog/LogtailClient.php
 */
class LogtailClient
{
	public const CURL_RETRYABLE_ERROR_CODES = [
		CURLE_COULDNT_RESOLVE_HOST,
		CURLE_COULDNT_CONNECT,
		CURLE_HTTP_NOT_FOUND,
		CURLE_READ_ERROR,
		CURLE_OPERATION_TIMEOUTED,
		CURLE_HTTP_POST_ERROR,
		CURLE_SSL_CONNECT_ERROR,
	];

	private CurlHandle|false $handle = false;

	public function __construct(private readonly LogtailConfiguration $configuration)
	{
	}

	private function getHandle(): CurlHandle
	{
		if ($this->handle === false) {
			$this->handle = $this->initCurlHandle();
		}

		return $this->handle;
	}

	public function send(mixed $data): void
	{
		curl_setopt($this->getHandle(), CURLOPT_POSTFIELDS, $data);
		curl_setopt($this->getHandle(), CURLOPT_RETURNTRANSFER, true);

		$this->tryExecute($this->getHandle(), 5, false);
	}

	private function initCurlHandle(): CurlHandle
	{
		$handle = curl_init();
		if (!$handle) {
			throw new LogicException('Could not initialize curl handle');
		}

		$headers = [
			'Content-Type: application/json',
			"Authorization: Bearer {$this->configuration->sourceToken}",
		];

		curl_setopt($handle, CURLOPT_URL, $this->configuration->endpoint);
		curl_setopt($handle, CURLOPT_POST, true);
		curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);

		return $handle;
	}

	private function tryExecute(CurlHandle $ch, int $retries = 5, bool $closeAfterDone = true): bool|string
	{
		while ($retries--) {
			$curlResponse = curl_exec($ch);
			if ($curlResponse === false) {
				$curlErrno = curl_errno($ch);

				if ($retries > 0 && in_array($curlErrno, self::CURL_RETRYABLE_ERROR_CODES, true)) {
					continue;
				}

				$curlError = curl_error($ch);

				if ($closeAfterDone) {
					curl_close($ch);
				}

				throw new RuntimeException(sprintf('Curl error (code %d): %s', $curlErrno, $curlError));
			}

			if ($closeAfterDone) {
				curl_close($ch);
			}

			return $curlResponse;
		}

		return false;
	}
}
