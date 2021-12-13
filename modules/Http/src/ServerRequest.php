<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayList;
use Elephox\Collection\ArrayMap;
use Elephox\Collection\Contract\GenericList;
use Elephox\Collection\Contract\GenericMap;
use Elephox\Http\Contract\Cookie;
use Elephox\Stream\Contract\Stream;
use Elephox\Support\MimeType;
use JetBrains\PhpStorm\Immutable;
use JsonException;

#[Immutable]
class ServerRequest extends Request implements Contract\ServerRequest
{
	/** @var ArrayList<Contract\UploadedFile> $files */
	protected ArrayList $files;

	/**
	 * @param Contract\RequestMethod $method
	 * @param Contract\Url|null $url
	 * @param Contract\RequestHeaderMap|null $headers
	 * @param Stream|null $body
	 * @param string $protocolVersion
	 * @param bool $inferHostHeader
	 * @param iterable<Contract\UploadedFile> $files
	 */
	public function __construct(
		Contract\RequestMethod $method = RequestMethod::GET,
		?Contract\Url $url = null,
		?Contract\RequestHeaderMap $headers = null,
		?Stream $body = null,
		string $protocolVersion = "1.1",
		bool $inferHostHeader = true,
		iterable $files = [],
	)
	{
		parent::__construct($method, $url, $headers, $body, $protocolVersion, $inferHostHeader);

		$this->files = new ArrayList($files);
	}

	public function getServerParamsMap(): GenericMap
	{
		return new ArrayMap($_SERVER);
	}

	public function getCookies(): GenericList
	{
		return $this->headers->get(HeaderName::Cookie);
	}

	public function withCookies(iterable $cookies): static
	{
		$headers = (clone $this->headers)->asRequestHeaders();
		$cookieHeader = $headers->get(HeaderName::Cookie);
		$cookieHeader->addAll($cookies);

		return new self($this->getRequestMethod()->copy(), clone $this->getUrl(), $headers, clone $this->getBody(), $this->getProtocolVersion(), files: clone $this->files);
	}

	public function withCookie(Cookie $cookie): static
	{
		return $this->withCookies([$cookie]);
	}

	public function getUploadedFiles(): ArrayList
	{
		return $this->files;
	}

	public function withUploadedFiles(iterable $uploadedFiles): static
	{
		$clonedFiles = clone $this->files;
		$clonedFiles->addAll($uploadedFiles);

		return new self($this->getRequestMethod()->copy(), clone $this->getUrl(), (clone $this->headers)->asRequestHeaders(), clone $this->getBody(), $this->getProtocolVersion(), files: $clonedFiles);
	}

	/**
	 * @template T of object
	 *
	 * @return array|array<T>|T|null
	 */
	public function getParsedBody(): null|array|object
	{
		if (!$this->headers->has(HeaderName::ContentType)) {
			return null;
		}

		switch ($this->headers->get(HeaderName::ContentType)->first()) {
			case MimeType::Applicationjson->value:
				try {
					/** @var T|array<T>|array|object|null */
					return json_decode($this->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);
				} catch (JsonException) {
					return null;
				}
			case MimeType::Applicationxwwwformurlencoded->value:
				parse_str($this->getBody()->getContents(), $parsedBody);

				/** @var array<T>|array */
				return $parsedBody;
			default:
				return null;
		}
	}
}
