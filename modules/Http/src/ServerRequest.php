<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Mimey\MimeType;
use Elephox\Stream\Contract\Stream;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use JsonException;
use Psr\Http\Message\UploadedFileInterface;
use InvalidArgumentException;

#[Immutable]
class ServerRequest extends Request implements Contract\ServerRequest
{
	#[Pure]
	public static function build(): ServerRequestBuilder
	{
		return new ServerRequestBuilder();
	}

	#[Pure]
	public function __construct(
		string $protocolVersion,
		Contract\HeaderMap $headers,
		Stream $body,
		RequestMethod $method,
		Url $url,
		public readonly Contract\ParameterMap $parameters,
		public readonly Contract\CookieMap $cookies,
		public readonly ?Contract\SessionMap $session,
		public readonly Contract\UploadedFileMap $uploadedFiles,
	) {
		parent::__construct($protocolVersion, $headers, $body, $method, $url);
	}

	#[Pure]
	public function with(): Contract\ServerRequestBuilder
	{
		/** @psalm-suppress ImpureMethodCall */
		return new ServerRequestBuilder(
			$this->protocolVersion,
			new HeaderMap($this->headers->toArray()),
			$this->body,
			$this->method,
			$this->url->with()->get(),
			ParameterMap::fromGlobals(
				$this->parameters->allFrom(ParameterSource::Post)->toArray(),
				$this->parameters->allFrom(ParameterSource::Get)->toArray(),
				$this->parameters->allFrom(ParameterSource::Server)->toArray(),
				$this->parameters->allFrom(ParameterSource::Env)->toArray(),
			),
			new CookieMap($this->cookies->select(static fn (Contract\Cookie $c) => new Cookie($c->getName(), $c->getValue(), $c->getExpires(), $c->getPath(), $c->getDomain(), $c->isSecure(), $c->isHttpOnly(), $c->getSameSite(), $c->getMaxAge()))->toArray()),
			$this->session !== null ? SessionMap::fromGlobals($this->session->toArray()) : null,
			new UploadedFileMap($this->uploadedFiles->toArray()),
		);
	}

	#[Pure]
	public function getParameterMap(): Contract\ParameterMap
	{
		return $this->parameters;
	}

	#[Pure]
	public function getCookieMap(): Contract\CookieMap
	{
		return $this->cookies;
	}

	#[Pure]
	public function getUploadedFileMap(): Contract\UploadedFileMap
	{
		return $this->uploadedFiles;
	}

	#[Pure]
	public function getSessionMap(): ?Contract\SessionMap
	{
		return $this->session;
	}

	#[Pure]
	public function getServerParams(): array
	{
		/** @psalm-suppress ImpureMethodCall */
		return $this->getParameterMap()->allFrom(ParameterSource::Server)->toArray();
	}

	#[Pure]
	public function getCookieParams(): array
	{
		/** @psalm-suppress ImpureMethodCall */
		return $this->getCookieMap()->select(static fn (Contract\Cookie $c) => $c->getValue())->toArray();
	}

	#[Pure]
	public function withCookieParams(array $cookies): static
	{
		$builder = $this->with();

		/**
		 * @var string $name
		 * @var string $value
		 */
		foreach ($cookies as $name => $value) {
			/** @psalm-suppress ImpureMethodCall */
			$builder->cookie(new Cookie($name, $value));
		}

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $builder->get();
	}

	#[Pure]
	public function getQueryParams(): array
	{
		/** @psalm-suppress ImpureMethodCall */
		return $this->getParameterMap()->allFrom(ParameterSource::Get)->toArray();
	}

	#[Pure]
	public function withQueryParams(array $query): static
	{
		$builder = $this->with();

		foreach ($query as $key => $value) {
			assert(is_string($key));
			assert(is_string($value) || is_int($value) || is_array($value));

			/** @psalm-suppress ImpureMethodCall */
			$builder->parameter($key, $value, ParameterSource::Get);
		}

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $builder->get();
	}

	#[Pure]
	public function getUploadedFiles(): array
	{
		/** @psalm-suppress ImpureMethodCall */
		return $this->getUploadedFileMap()->toArray();
	}

	#[Pure]
	public function withUploadedFiles(array $uploadedFiles): static
	{
		$builder = $this->with();

		/**
		 * @var UploadedFileInterface $uploadedFile
		 */
		foreach ($uploadedFiles as $name => $uploadedFile) {
			assert(is_string($name));

			if (!($uploadedFile instanceof Contract\UploadedFile)) {
				throw new InvalidArgumentException("Only Contract\UploadedFile instances are supported");
			}

			/** @psalm-suppress ImpureMethodCall */
			$builder->uploadedFile($name, $uploadedFile);
		}

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $builder->get();
	}

	#[Pure]
	public function getParsedBody(): null|array|object
	{
		/** @psalm-suppress ImpureMethodCall */
		return match ($this->getContentType()?->getValue()) {
			MimeType::ApplicationJson->value => $this->getBodyAsJson(),
//			MimeType::ApplicationXml->value => $this->getBodyAsXml(),
			default => null,
		};
	}

	#[Pure]
	protected function getBodyAsJson(): ?array
	{
		try {
			/** @psalm-suppress ImpureMethodCall */
			$body = $this->getBody()->getContents();

			/**
			 * @psalm-suppress ImpureFunctionCall
			 *
			 * @var array<array-key, mixed>
			 */
			return json_decode($body, true, flags: JSON_THROW_ON_ERROR);
		} catch (JsonException) {
			return null;
		}
	}

	#[Pure]
	public function withParsedBody($data): static
	{
		try {
			assert($data === null || is_array($data) || is_object($data));

			$builder = $this->with();

			if (is_array($data) || is_object($data)) {
				/** @psalm-suppress ImpureMethodCall */
				$builder->jsonBody($data);
			} else {
				/** @psalm-suppress ImpureMethodCall */
				$builder->textBody('');
			}

			/**
			 * @psalm-suppress ImpureMethodCall
			 *
			 * @var static
			 */
			return $builder->get();
		} catch (JsonException $e) {
			throw new InvalidArgumentException('Failed to encode parsed body', previous: $e);
		}
	}

	#[Pure]
	public function getAttributes(): array
	{
		/** @psalm-suppress ImpureMethodCall */
		return $this->parameters->allFrom(ParameterSource::Attribute)->toArray();
	}

	#[Pure]
	public function getAttribute($name, $default = null): mixed
	{
		/** @psalm-suppress ImpureMethodCall */
		return $this->parameters->get($name, ParameterSource::Attribute) ?? $default;
	}

	#[Pure]
	public function withAttribute($name, $value): static
	{
		assert(is_string($name));
		assert(is_string($value) || is_int($value) || is_array($value));

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->parameter($name, $value, ParameterSource::Attribute)->get();
	}

	#[Pure]
	public function withoutAttribute($name): static
	{
		assert(is_string($name));

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->removedParameter($name, ParameterSource::Attribute)->get();
	}
}
