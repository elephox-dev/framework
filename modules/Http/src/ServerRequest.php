<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayList;
use Elephox\Collection\ArrayMap;
use Elephox\Http\Contract\Cookie;
use Elephox\Stream\Contract\Stream;
use Elephox\Support\MimeType;
use JetBrains\PhpStorm\Pure;
use JsonException;

/**
 * @psalm-consistent-constructor
 */
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
	 * @param list<Contract\UploadedFile> $files
	 */
	public function __construct(
		Contract\RequestMethod $method = RequestMethod::GET,
		?Contract\Url $url = null,
		?Contract\RequestHeaderMap $headers = null,
		?Stream $body = null,
		string $protocolVersion = "1.1",
		bool $inferHostHeader = true,
		array $files = [],
	)
	{
		parent::__construct($method, $url, $headers, $body, $protocolVersion, $inferHostHeader);

		$this->files = new ArrayList($files);
	}

	#[Pure] public function getServerParamsMap(): ArrayMap
	{
		/** @var ArrayMap<string, string> */
		return new ArrayMap($_SERVER);
	}

	#[Pure] public function getCookies(): ArrayList
	{
		/** @var ArrayList<Cookie> */
		return $this->headers->get(HeaderName::Cookie);
	}

	public function withCookies(iterable $cookies): static
	{
		$headers = (clone $this->headers)->asRequestHeaders();
		/** @var ArrayList<Cookie> $cookieHeader */
		$cookieHeader = $headers->get(HeaderName::Cookie);
		$cookieHeader->addAll($cookies);

		return new static($this->getRequestMethod(), clone $this->getUrl(), $headers, clone $this->getBody(), $this->getProtocolVersion(), files: (clone $this->files)->asArray());
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

		return new static($this->getRequestMethod(), clone $this->getUrl(), (clone $this->headers)->asRequestHeaders(), clone $this->getBody(), $this->getProtocolVersion(), files: $clonedFiles->asArray());
	}

	/**
	 * @return array|object|null
	 */
	public function getParsedBody(): null|array|object
	{
		if (!$this->headers->has(HeaderName::ContentType)) {
			return null;
		}

		switch ($this->headers->get(HeaderName::ContentType)->first()) {
			case MimeType::Applicationjson->value:
				try {
					/** @var array|object|null */
					return json_decode($this->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);
				} catch (JsonException) {
					return null;
				}
			case MimeType::Applicationxwwwformurlencoded->value:
				/** @psalm-suppress ImpureMethodCall */
				parse_str($this->getBody()->getContents(), $parsedBody);

				/** @var array */
				return $parsedBody;
			default:
				return null;
		}
	}
}
