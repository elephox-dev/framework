<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Stream\Contract\Stream;
use Elephox\Stream\EmptyStream;
use Elephox\Stream\ResourceStream;
use JetBrains\PhpStorm\Pure;
use RuntimeException;

/**
 * @psalm-consistent-constructor
 */
class ServerRequestBuilder extends RequestBuilder implements Contract\ServerRequestBuilder
{
	#[Pure]
	public function __construct(
		?string $protocolVersion = null,
		?Contract\HeaderMap $headers = null,
		?Stream $body = null,
		?RequestMethod $method = null,
		?Url $url = null,
		protected ?Contract\ParameterMap $parameters = null,
		protected ?Contract\CookieMap $cookies = null,
		protected ?Contract\SessionMap $session = null,
		protected ?Contract\UploadedFileMap $uploadedFiles = null,
	) {
		parent::__construct($protocolVersion, $headers, $body, $method, $url);
	}

	public function parameter(string $key, array|int|string $value, ParameterSource $source): static
	{
		if ($this->parameters === null) {
			$this->parameters = new ParameterMap();
		}

		$this->parameters->put($key, $source, $value);

		return $this;
	}

	public function parameters(Contract\ParameterMap $parameters): static
	{
		$this->parameters = $parameters;

		return $this;
	}

	public function getParameters(): ?Contract\ParameterMap
	{
		return $this->parameters;
	}

	public function cookie(Contract\Cookie $cookie): static
	{
		if ($this->cookies === null) {
			$this->cookies = new CookieMap();
		}

		$this->cookies->put($cookie->getName(), $cookie);

		return $this;
	}

	public function cookies(Contract\CookieMap $cookies): static
	{
		$this->cookies = $cookies;

		return $this;
	}

	public function getCookies(): ?Contract\CookieMap
	{
		return $this->cookies;
	}

	public function sessionParam(string $name, mixed $value): static
	{
		if ($this->session === null) {
			$this->session = SessionMap::start();
		}

		$this->session->put($name, $value);

		return $this;
	}

	public function session(?Contract\SessionMap $session): static
	{
		$this->session = $session;

		return $this;
	}

	public function getSession(): ?Contract\SessionMap
	{
		return $this->session;
	}

	public function uploadedFile(string $name, Contract\UploadedFile $uploadedFile): static
	{
		if ($this->uploadedFiles === null) {
			$this->uploadedFiles = new UploadedFileMap();
		}

		$this->uploadedFiles->put($name, $uploadedFile);

		return $this;
	}

	public function uploadedFiles(Contract\UploadedFileMap $uploadedFiles): static
	{
		$this->uploadedFiles = $uploadedFiles;

		return $this;
	}

	public function getUploadedFiles(): ?Contract\UploadedFileMap
	{
		return $this->uploadedFiles;
	}

	public function get(): Contract\ServerRequest
	{
		return new ServerRequest(
			$this->protocolVersion ?? self::DefaultProtocolVersion,
			$this->headers ?? new HeaderMap(),
			$this->body ?? new EmptyStream(),
			$this->method ?? RequestMethod::GET,
			$this->url ?? throw self::missingParameterException('url'),
			$this->parameters ?? new ParameterMap(),
			$this->cookies ?? new CookieMap(),
			$this->session,
			$this->uploadedFiles ?? new UploadedFileMap(),
		);
	}

	public static function fromGlobals(
		?Contract\ParameterMap $parameters = null,
		?Contract\HeaderMap $headers = null,
		?Contract\CookieMap $cookies = null,
		?Contract\SessionMap $session = null,
		?Contract\UploadedFileMap $files = null,
		?string $protocolVersion = AbstractMessageBuilder::DefaultProtocolVersion,
		?Stream $body = null,
		?RequestMethod $requestMethod = null,
		?Url $requestUrl = null,
	): Contract\ServerRequest {
		$parameters ??= ParameterMap::fromGlobals();
		$headers ??= HeaderMap::fromGlobals();
		$cookies ??= CookieMap::fromGlobals();
		$session ??= SessionMap::fromGlobals();
		$files ??= UploadedFileMap::fromGlobals();

		$builder = new self();
		$builder->parameters($parameters);
		$builder->headerMap($headers);
		$builder->cookies($cookies);
		$builder->session($session);
		$builder->uploadedFiles($files);

		if ($body === null) {
			$readonlyInput = fopen('php://input', 'rb');
			if ($readonlyInput === false) {
				throw new RuntimeException('Unable to open php://input');
			}

			if ($parameters->has('CONTENT_LENGTH', ParameterSource::Server)) {
				$contentLength = (int) $parameters->get('CONTENT_LENGTH', ParameterSource::Server);
				if ($contentLength > 0) {
					$builder->body(new ResourceStream($readonlyInput, size: $contentLength));
				}
			} else {
				$builder->body(new ResourceStream($readonlyInput));
			}
		} else {
			$builder->body($body);
		}

		if ($protocolVersion === null) {
			if ($parameters->has('SERVER_PROTOCOL', ParameterSource::Server)) {
				$protocol = (string) $parameters->get('SERVER_PROTOCOL', ParameterSource::Server);
				$protocolParts = explode('/', $protocol, 2);
				if (count($protocolParts) === 2) {
					$builder->protocolVersion($protocolParts[1]);
				}
			}
		} else {
			$builder->protocolVersion($protocolVersion);
		}

		if ($requestMethod === null) {
			if ($parameters->has('REQUEST_METHOD', ParameterSource::Server)) {
				$requestMethodType = RequestMethod::tryFrom((string) $parameters->get('REQUEST_METHOD', ParameterSource::Server));
				if ($requestMethodType !== null) {
					$builder->requestMethod($requestMethodType);
				}
			}
		} else {
			$builder->requestMethod($requestMethod);
		}

		if ($requestUrl === null) {
			if ($parameters->has('REQUEST_URI', ParameterSource::Server)) {
				$builder->requestUrl(Url::fromString((string) $parameters->get('REQUEST_URI', ParameterSource::Server)));
			}
		} else {
			$builder->requestUrl($requestUrl);
		}

		return $builder->get();
	}

	#[Pure]
	public static function fromRequest(Contract\Request $request): static
	{
		return new static(
			$request->getProtocolVersion(),
			$request->getHeaderMap(),
			$request->getBody(),
			$request->getRequestMethod(),
			$request->getUrl(),
		);
	}
}
