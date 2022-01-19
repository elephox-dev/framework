<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Http\Url;

class UrlTemplate
{
	public function __construct(
		private string $source
	) {
	}

	public function getSource(): string
	{
		return $this->source;
	}

	/**
	 * @param iterable<string, string> $parameters
	 * @return Url
	 * @throws \Safe\Exceptions\PcreException
	 * @throws \Safe\Exceptions\StringsException
	 */
	public function compile(iterable $parameters): Url
	{
		$source = $this->source;
		foreach ($parameters as $key => $value) {
			$source = str_replace("\{$key}", $value, $source);
		}

		return Url::fromString($source);
	}

	/**
	 * @throws \Safe\Exceptions\PcreException
	 */
	public function matches(Url $url): bool
	{
		$source = $this->getSanitizedSource();

		return \Safe\preg_match("/^$source$/", (string)$url) === 1;
	}

	/**
	 * @throws \Safe\Exceptions\PcreException
	 */
	public function getValues(Url $url): array
	{
		$source = $this->getSanitizedSource();

		\Safe\preg_match_all("/^$source$/", (string)$url, $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL);
		/**
		 * @var list<list<string>> $matches
		 */

		return $matches[0];
	}

	/**
	 * @throws \Safe\Exceptions\PcreException
	 */
	private function getSanitizedSource(): string
	{
		$source = $this->source;
		$source = str_starts_with($source, '/') ? $source : "/$source";

		/** @var string */
		return \Safe\preg_replace('/\//', '\\/', $source);
	}
}
