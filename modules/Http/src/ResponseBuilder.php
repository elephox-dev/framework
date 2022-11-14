<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\DefaultEqualityComparer;
use Elephox\Files\Contract\File as FileContract;
use Elephox\Files\File;
use Elephox\Mimey\MimeType;
use Elephox\Mimey\MimeTypeInterface;
use Elephox\Stream\Contract\Stream;
use Elephox\Stream\EmptyStream;
use Elephox\Stream\StringStream;
use JetBrains\PhpStorm\Pure;
use JsonException;
use LogicException;
use Throwable;

/**
 * @psalm-consistent-constructor
 */
class ResponseBuilder extends AbstractMessageBuilder implements Contract\ResponseBuilder
{
	use DerivesContentTypeFromHeaderMap;

	#[Pure]
	public function __construct(
		?string $protocolVersion = null,
		?Contract\HeaderMap $headers = null,
		?Stream $body = null,
		protected ?ResponseCode $responseCode = null,
		protected ?Throwable $exception = null,
	) {
		parent::__construct($protocolVersion, $headers, $body);
	}

	public function responseCode(ResponseCode $responseCode): static
	{
		$this->responseCode = $responseCode;

		return $this;
	}

	public function getResponseCode(): ?ResponseCode
	{
		return $this->responseCode;
	}

	public function contentType(?MimeTypeInterface $mimeType): static
	{
		if ($this->headers === null && $mimeType !== null) {
			$this->addedHeader(HeaderName::ContentType->value, $mimeType->getValue());
		} elseif ($this->headers !== null) {
			$headerSet = $this->headers->containsKey(HeaderName::ContentType->value, DefaultEqualityComparer::equalsIgnoreCase(...));
			if ($headerSet && $mimeType === null) {
				$this->headers->remove(HeaderName::ContentType->value);
			} elseif ($mimeType !== null) {
				$this->headers->put(HeaderName::ContentType->value, [$mimeType->getValue()]);
			}
		}

		return $this;
	}

	public function exception(?Throwable $exception, ?ResponseCode $responseCode = ResponseCode::InternalServerError): static
	{
		$this->exception = $exception;

		if ($responseCode) {
			$this->responseCode($responseCode);
		}

		return $this;
	}

	public function getException(): ?Throwable
	{
		return $this->exception;
	}

	public function textBody(string $content, ?MimeTypeInterface $mimeType = MimeType::TextPlain): static
	{
		$this->body(new StringStream($content));

		if ($mimeType) {
			$this->contentType($mimeType);
		}

		return $this;
	}

	/**
	 * @throws JsonException
	 *
	 * @param null|MimeTypeInterface $mimeType
	 * @param array|object $data
	 */
	public function jsonBody(array|object $data, ?MimeTypeInterface $mimeType = MimeType::ApplicationJson): static
	{
		$json = json_encode($data, JSON_THROW_ON_ERROR);

		return $this->textBody($json, $mimeType);
	}

	public function htmlBody(string $content, ?MimeTypeInterface $mimeType = MimeType::TextHtml): static
	{
		return $this->textBody($content, $mimeType);
	}

	public function fileBody(string|FileContract $path, ?MimeTypeInterface $mimeType = MimeType::ApplicationOctetStream): static
	{
		$this->body(File::openStream($path));

		if ($mimeType) {
			$this->contentType($mimeType);
		}

		return $this;
	}

	public function get(): Contract\Response
	{
		return new Response(
			$this->protocolVersion ?? self::DefaultProtocolVersion,
			$this->headers ?? new HeaderMap(),
			$this->body ?? new EmptyStream(),
			$this->responseCode ?? throw new LogicException('Response code is not set.'),
			$this->exception,
		);
	}
}
