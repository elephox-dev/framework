<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Http\Contract;
use Elephox\Http\Url;

class UrlTemplate
{
	public const ParamNameExtractor = '/[^\{]*\{([^\}]+)\}/';
	public const SourceTransformMatch = '/(\{[^\}]+\})/';

	public function __construct(
		private string $source
	)
	{
	}

	public function getSource(): string
	{
		return $this->source;
	}

	/**
	 * @param iterable<string, string> $parameters
	 * @return Contract\Url
	 */
	public function compile(iterable $parameters): Contract\Url
	{
		$source = $this->source;
		foreach ($parameters as $key => $value) {
			$source = str_replace("\{$key}", $value, $source);
		}

		return Url::fromString($source);
	}

	public function matches(Contract\Url $url): bool
	{
		$source = preg_replace(self::SourceTransformMatch, '.*?', $this->source);
		$source = str_starts_with($source, '/') ? $source : "/$source";
		$source = preg_replace('/\//', '\\/', $source);

		return preg_match("/^$source$/", (string)$url) === 1;
	}

	public function getValues(Contract\Url $url): array
	{
		// extract url parameters from template and return a named array
		$source = preg_replace(self::SourceTransformMatch, '(.*?)', $this->source);

		preg_match_all($source, (string)$url, $matches);

		return $matches;
	}
}
