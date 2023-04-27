<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Elephox\Collection\ArrayList;
use Elephox\Collection\ArrayMap;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Collection\Contract\GenericReadonlyList;
use Elephox\OOR\Range;
use Elephox\Web\Routing\Contract\RouteTemplate as RouteTemplateContract;

readonly class RouteTemplate implements RouteTemplateContract
{
	public const INVALID_NAME_CHARACTERS = ['[', ']', '{', '}', '<', '>', '#', '/', '\\', '\'', '"'];

	public static function parse(string $template, ?self $parent = null): self
	{
		/**
		 * @psalm-suppress RedundantCondition
		 * @psalm-suppress TypeDoesNotContainNull
		 */
		$parentVariables = $parent?->variables->toList() ?? [];

		/** @var ArrayList<RouteTemplateVariable> $variables */
		$variables = new ArrayList($parentVariables);

		/**
		 * @psalm-suppress RedundantCondition
		 * @psalm-suppress TypeDoesNotContainNull
		 */
		$parentDynamics = $parent?->dynamics->toList() ?? [];

		/** @var ArrayMap<string, Range> $dynamics */
		$dynamics = new ArrayMap($parentDynamics);

		$parentTemplate = $parent?->source;
		$normalizedTemplate = '/' . trim($template, '/');
		if ($parentTemplate === null) {
			$parentOffset = 0;

			$combinedTemplate = $normalizedTemplate;
		} else {
			$parentOffset = mb_strlen($parentTemplate);
			if ($template === '') {
				$combinedTemplate = $parentTemplate;
			} elseif ($parentTemplate !== '/') {
				$combinedTemplate = $parentTemplate . $normalizedTemplate;
			} else {
				$parentOffset = 0;

				$combinedTemplate = $normalizedTemplate;
			}
		}

		$state = 'segment';
		$i = $parentOffset;
		$start = $i;
		$name = '';
		$type = null;
		$iMax = mb_strlen($combinedTemplate);
		while ($i < $iMax) {
			$c = $combinedTemplate[$i];
			if ($state === 'segment' || $state === 'end') {
				if ($c === '[') {
					$name = '';
					$state = 'dynamic';
					$start = $i;
					$i++;

					continue;
				}

				if ($c === '{') {
					$name = '';
					$type = null;
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

					if ($type === '') {
						throw new InvalidRouteTemplateException($template, 'empty variable type');
					}

					$variables->add(new RouteTemplateVariable($name, $type ?? 'string', new Range($start, $i)));
					$name = '';
					$type = null;
					$state = 'end';
					$i++;
					$start = $i;

					continue;
				}

				if ($c === ':') {
					$type = '';
					$i++;

					continue;
				}

				/** @psalm-suppress ParadoxicalCondition */
				if (in_array($c, self::INVALID_NAME_CHARACTERS, true)) {
					throw new InvalidRouteTemplateException($template, 'invalid character in variable name');
				}

				if ($type !== null) {
					$type .= $c;
				} else {
					$name .= $c;
				}
			} else {
				/** @psalm-suppress RedundantCondition */
				assert($state === 'dynamic', 'Unexpected parser state');

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

				/** @psalm-suppress ParadoxicalCondition */
				if (in_array($c, self::INVALID_NAME_CHARACTERS, true)) {
					throw new InvalidRouteTemplateException($template, 'invalid character in dynamics name');
				}

				$name .= $c;
			}

			$i++;
		}

		if ($state === 'segment' || $state === 'end') {
			return new self($combinedTemplate, $variables, $dynamics);
		}

		if ($state === 'variable') {
			throw new InvalidRouteTemplateException($template, 'missing closing curly brace \'}\'');
		}

		assert($state === 'dynamic', 'Unexpected parser state');

		throw new InvalidRouteTemplateException($template, 'missing closing bracket \']\'');
	}

	/**
	 * @param ArrayList<RouteTemplateVariable> $variables
	 * @param ArrayMap<string, Range> $dynamics
	 */
	public function __construct(
		private string $source,
		private ArrayList $variables,
		private ArrayMap $dynamics,
	) {
	}

	public function getSource(): string
	{
		return $this->source;
	}

	public function getVariables(): GenericReadonlyList
	{
		return $this->variables;
	}

	public function renderRegExp(array $dynamics): string
	{
		/** @var GenericKeyedEnumerable<string, Range> $variableRegexes */
		$variableRegexes = $this->variables
			->selectKeys(static function (int $index, RouteTemplateVariable $variable) {
				$name = $variable->name;
				$pattern = $variable->getTypePattern();

				return "(?<$name>$pattern)";
			})
			->select(static fn (RouteTemplateVariable $variable) => $variable->position)
		;

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
