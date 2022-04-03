<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Collection\ArrayList;

/**
 * @extends ArrayList<Argument>
 */
class ArgumentList extends ArrayList
{
	public static function create(CommandTemplate $template, CommandInvocationArgumentsMap $argumentsMap): self
	{
		$arguments = new self();
		$usedKeys = [];

		/**
		 * @var ArgumentTemplate $argumentTemplate
		 */
		foreach ($template->argumentTemplates as $argumentTemplate) {
			if ($argumentsMap->has($argumentTemplate->name)) {
				if (in_array($argumentTemplate->name, $usedKeys, true)) {
					continue;
				}

				$arguments->add(Argument::fromTemplate($argumentTemplate, $argumentsMap->get($argumentTemplate->name)));
				$usedKeys[] = $argumentTemplate->name;

				continue;
			}

			if (!$argumentsMap->isEmpty()) {
				$argKey = $argumentsMap->firstKeyOrDefault(null, fn ($k) => !in_array($k, $usedKeys, true));
				if ($argKey !== null) {
					$arguments->add(Argument::fromTemplate($argumentTemplate, $argumentsMap->get($argKey)));
					$usedKeys[] = $argKey;

					continue;
				}
			}

			if (!$argumentTemplate->required) {
				$arguments->add(Argument::fromTemplate($argumentTemplate, $argumentTemplate->default));

				continue;
			}

			throw new RequiredArgumentMissingException($argumentTemplate->name);
		}

		return $arguments;
	}
}
