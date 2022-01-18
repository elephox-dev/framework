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
	 */
	public function compile(iterable $parameters): Url
	{
		$source = $this->source;
		foreach ($parameters as $key => $value) {
			$source = str_replace("\{$key}", $value, $source);
		}

		return Url::fromString($source);
	}

	public function matches(Url $url): bool
	{
		$source = $this->getSanitizedSource();

		return \Safe\preg_match("/^$source$/", (string)$url) === 1;
	}

	public function getValues(Url $url): array
	{
		$source = $this->getSanitizedSource();

		\Safe\preg_match_all("/^$source$/", (string)$url, $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL);

		return $matches[0];
	}

	private function getSanitizedSource(): string
	{
		$source = $this->source;
		$source = str_starts_with($source, '/') ? $source : "/$source";
		return \Safe\preg_replace('/\//', '\\/', $source);
	}
}
