<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Elephox\Collection\ArrayMap;
use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\OOR\Range;
use Elephox\Web\Routing\Contract\RouteTemplate as RouteTemplateContract;

readonly class RouteTemplate implements RouteTemplateContract
{
	private const INVALID_NAME_CHARACTERS = ['[', ']', '{', '}', '<', '>', '#', '/', '\\'];

	public static function parse(string $template, ?self $parent = null): self
	{
		/** @var ArrayMap<string, Range> $variables */
		$variables = new ArrayMap($parent?->variables->toArray() ?? []);

		/** @var ArrayMap<string, Range> $dynamics */
		$dynamics = new ArrayMap($parent?->dynamics->toArray() ?? []);

		$normalizedTemplate = '/' . trim($template, '/');
		$parentTemplate = $parent?->source ?? '';
		$combinedTemplate = $parentTemplate . $normalizedTemplate;
		$normalizedCombinedTemplate = '/' . trim($combinedTemplate, '/');

		$state = 'segment';
		$i = mb_strlen($parentTemplate) + 1;
		$start = mb_strlen($normalizedCombinedTemplate) + 1;
		$name = '';
		$iMax = mb_strlen($normalizedCombinedTemplate);
		while ($i < $iMax) {
			$c = $normalizedCombinedTemplate[$i];
			if ($state === 'segment' || $state === 'end') {
				if ($c === '[') {
					$state = 'dynamic';
					$start = $i;
					$i++;

					continue;
				}

				if ($c === '{') {
					$state = 'variable';
					$start = $i;
					$i++;

					continue;
				}

				if ($c === ']' || $c === '}') {
					throw new InvalidRouteTemplateException($template, 'stray closing bracket');
				}

				if ($c === '/') {
					if ($state === 'segment') {
						if ($name === '') {
							throw new InvalidRouteTemplateException($template, 'empty segment name');
						}

						$name = '';
					} else {
						$state = 'segment';
					}

					$i++;
					$start = $i;

					continue;
				}

				$name .= $c;
			} elseif ($state === 'variable') {
				if ($c === '}') {
					if ($name === '') {
						throw new InvalidRouteTemplateException($template, 'empty variable name');
					}

					$variables->put($name, new Range($start, $i));
					$name = '';
					$state = 'end';
					$i++;
					$start = $i;

					continue;
				}

				if (in_array($c, self::INVALID_NAME_CHARACTERS, true)) {
					throw new InvalidRouteTemplateException($template, 'invalid character in variable name');
				}

				$name .= $c;
			} elseif ($state === 'dynamic') {
				if ($c === ']') {
					if ($name === '') {
						throw new InvalidRouteTemplateException($template, 'empty dynamics name');
					}

					$dynamics->put($name, new Range($start, $i));
					$name = '';
					$state = 'end';
					$i++;
					$start = $i;

					continue;
				}

				if (in_array($c, self::INVALID_NAME_CHARACTERS, true)) {
					throw new InvalidRouteTemplateException($template, 'invalid character in dynamics name');
				}

				$name .= $c;
			}

			$i++;
		}

		if ($state === 'segment' || $state === 'end') {
			return new self($normalizedCombinedTemplate, $variables, $dynamics);
		}

		throw new InvalidRouteTemplateException($template, 'unexpected parser state');
	}

	/**
	 * @param ArrayMap<string, Range> $variables
	 * @param ArrayMap<string, Range> $dynamics
	 */
	public function __construct(
		private string $source,
		private ArrayMap $variables,
		private ArrayMap $dynamics,
	) {
	}

	public function getSource(): string
	{
		return $this->source;
	}

	public function getVariableNames(): GenericEnumerable
	{
		return $this->variables->keys();
	}

	public function getDynamicNames(): GenericEnumerable
	{
		return $this->dynamics->keys();
	}

	public function renderRegExp(array $dynamics): string
	{
		$variableRegexes = $this->variables->selectKeys(static fn (string $name) => "(?<$name>[^}/]+)");

		$dynamicsRegexes = $this->dynamics->selectKeys(function (string $name) use ($dynamics): string {
			if (array_key_exists($name, $dynamics)) {
				return $dynamics[$name];
			}

			throw new InvalidRouteTemplateException($this->source, "unknown dynamics name: [$name]");
		});

		$replacements = $variableRegexes
			->concat($dynamicsRegexes)
			->orderByDescending(static fn (Range $r) => $r->from)
		;

		$regex = $this->source;

		/**
		 * @var string $replacement
		 * @var Range $range
		 */
		foreach ($replacements as $replacement => $range) {
			$regex = substr_replace($regex, $replacement, $range->from, $range->length);
		}

		return "#^$regex$#i";
	}
}
