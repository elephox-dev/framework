<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Collection\ArrayMap;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Http\Url;
use RuntimeException;

class UrlTemplate implements Contract\UrlTemplate
{
	public function __construct(
		private string $source
	) {
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

	public function getValues(Url $url): GenericKeyedEnumerable
	{
		$source = $this->getSanitizedSource();

		$result = preg_match_all("/^$source$/", (string)$url, $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL);
		if ($result === false) {
			throw new RuntimeException('Failed to match URL template');
		}

		/** @var GenericKeyedEnumerable<string, string> */
		return ArrayMap::from($matches[0])->whereKey(fn ($key) => is_string($key));
	}

	private function getSanitizedSource(): string
	{
		$source = $this->source;

		return str_starts_with($source, '\\/') ? $source : "\\/$source";
	}
}
