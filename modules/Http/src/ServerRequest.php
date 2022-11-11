<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Stream\Contract\Stream;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\UploadedFileInterface;

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
			new CookieMap($this->cookies->select(fn (Contract\Cookie $c) => new Cookie($c->getName(), $c->getValue(), $c->getExpires(), $c->getPath(), $c->getDomain(), $c->isSecure(), $c->isHttpOnly(), $c->getSameSite(), $c->getMaxAge()))->toArray()),
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
		return $this->getCookieMap()->select(fn (Contract\Cookie $c) => $c->getValue())->toArray();
	}

	#[Pure]
	public function withCookieParams(array $cookies): static
	{
		/** @psalm-suppress ImpureMethodCall */
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
		/** @psalm-suppress ImpureMethodCall */
		$builder = $this->with();

		foreach ($query as $key => $value) {
			assert(is_string($key));
			assert(is_string($value) || is_int($value) || is_array($value));

			/** @psalm-suppress ImpureMethodCall */
			$builder->parameter($key, $value, ParameterSource::Get);
		}

		/**
		 * @psalm-suppress ImpureMethodCall
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
		/** @psalm-suppress ImpureMethodCall */
		$builder = $this->with();

		/**
		 * @var UploadedFileInterface $uploadedFile
		 */
		foreach ($uploadedFiles as $name => $uploadedFile) {
			if (!($uploadedFile instanceof Contract\UploadedFile)) {
				throw new \InvalidArgumentException("Only Contract\UploadedFile instances are supported");
			}

			/** @psalm-suppress ImpureMethodCall */
			$builder->uploadedFile($name, $uploadedFile);
		}

		/**
		 * @psalm-suppress ImpureMethodCall
		 * @var static
		 */
		return $builder->get();
	}

	#[Pure]
	public function getParsedBody()
	{
		/** @psalm-suppress ImpureMethodCall */
		// TODO: Implement getParsedBody() method.
	}

	#[Pure]
	public function withParsedBody($data): static
	{
		/** @psalm-suppress ImpureMethodCall */
		// TODO: Implement withParsedBody() method.
	}

	#[Pure]
	public function getAttributes(): static
	{
		/** @psalm-suppress ImpureMethodCall */
		// TODO: Implement getAttributes() method.
	}

	#[Pure]
	public function getAttribute($name, $default = null)
	{
		/** @psalm-suppress ImpureMethodCall */
		// TODO: Implement getAttribute() method.
	}

	#[Pure]
	public function withAttribute($name, $value): static
	{
		/** @psalm-suppress ImpureMethodCall */
		// TODO: Implement withAttribute() method.
	}

	#[Pure]
	public function withoutAttribute($name)
	{
		/** @psalm-suppress ImpureMethodCall */
		// TODO: Implement withoutAttribute() method.
	}
}
