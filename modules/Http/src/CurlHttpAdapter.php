<?php
declare(strict_types=1);

namespace Elephox\Http;

use CurlHandle;
use Exception;
use Elephox\Http\Contract\HttpAdapter;

class CurlHttpAdapter implements Contract\HttpAdapter
{
	protected false|CurlHandle $handle = false;
	protected false|string|null $response = null;
	protected ?string $error = null;

	/**
	 * @throws \Exception
	 */
	public function setUrl(string $url): HttpAdapter
	{
		if (!$this->handle) {
			throw new Exception('CurlHttpAdapter::prepare() must be called before CurlHttpAdapter::setUrl()');
		}

		curl_setopt($this->handle, CURLOPT_URL, $url);

		return $this;
	}

	/**
	 * @throws \Exception
	 */
	public function setMethod(string $method): HttpAdapter
	{
		if (!$this->handle) {
			throw new Exception('CurlHttpAdapter::prepare() must be called before CurlHttpAdapter::setMethod()');
		}

		curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, $method);

		return $this;
	}

	/**
	 * @throws \Exception
	 */
	public function setHeaders(array $headers): HttpAdapter
	{
		if (!$this->handle) {
			throw new Exception('CurlHttpAdapter::prepare() must be called before CurlHttpAdapter::setHeaders()');
		}

		curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);

		return $this;
	}

	/**
	 * @throws \Exception
	 */
	public function setBody(?string $body): HttpAdapter
	{
		if (!$this->handle) {
			throw new Exception('CurlHttpAdapter::prepare() must be called before CurlHttpAdapter::setBody()');
		}

		curl_setopt($this->handle, CURLOPT_POSTFIELDS, $body);

		return $this;
	}

	public function prepare(): HttpAdapter
	{
		$this->handle = curl_init();

		curl_setopt_array(
			$this->handle,
			[
				CURLOPT_HEADER => true,
				CURLOPT_RETURNTRANSFER => true,
			]
		);

		return $this;
	}

	/**
	 * @throws \Exception
	 */
	public function send(): bool
	{
		if (!$this->handle) {
			throw new Exception('CurlHttpAdapter::prepare() must be called before CurlHttpAdapter::send()');
		}

		/** @var false|string $result */
		$result = curl_exec($this->handle);
		if ($result === false) {
			$this->error = curl_error($this->handle);

			return false;
		}

		$this->response = $result;

		return true;
	}

	/**
	 * @throws \Exception
	 */
	public function getResponse(): ?string
	{
		if (!$this->handle) {
			throw new Exception('CurlHttpAdapter::prepare() must be called before CurlHttpAdapter::getResponse()');
		}

		if ($this->response === false) {
			throw new Exception('CurlHttpAdapter::send() failed. Check CurlHttpAdapter::getLastError() for more information');
		}

		return $this->response;
	}

	public function cleanup(): void
	{
		if (!$this->handle) {
			return;
		}

		curl_close($this->handle);

		$this->handle = false;
		$this->response = null;
		$this->error = null;
	}

	public function getLastError(): ?string
	{
		return $this->error;
	}
}
