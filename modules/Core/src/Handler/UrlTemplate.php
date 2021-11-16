<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Collection\Contract\GenericMap;
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
	 * @param GenericMap<string, string|int|float|bool> $parameters
	 * @return Contract\Url
	 */
	public function compile(GenericMap $parameters): Contract\Url
	{
		$source = $this->source;
		foreach ($parameters as $key => $value) {
			$source = str_replace("\{$key}", $value, $source);
		}

		return Url::fromString($source);
	}

	public function matches(string $url): bool
	{
		$source = preg_replace(self::SourceTransformMatch, '*', $this->source);

		return preg_match("/^$source$/", $url) === 1;
	}
}
