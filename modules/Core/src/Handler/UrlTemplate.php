<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Http\Url;

class UrlTemplate
{
	public function __construct(
		private string $source
	)
	{
	}

	public function getSource(): string
	{
		return $this->source;
	}

	public function matches(Url $url): bool
	{
		$source = $this->getSanitizedSource();

		return preg_match("/^$source$/", (string)$url) === 1;
	}

	public function getValues(Url $url): array
	{
		$source = $this->getSanitizedSource();

		preg_match_all("/^$source$/", (string)$url, $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL);

		return $matches[0];
	}

	public function getValue(Url $url, string $name): null|string
	{
		$values = $this->getValues($url);

		/** @var mixed $value */
		$value = $values[$name];

		if (is_string($value)) {
			return $value;
		}

		return null;
	}

	private function getSanitizedSource(): string
	{
		$source = $this->source;

		return str_starts_with($source, '/') ? $source : "\\/$source";
	}
}
