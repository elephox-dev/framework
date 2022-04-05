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

		foreach ($argumentsMap->whereKey(fn($k) => is_string($k)) as $key => $value) {
			$matchingTemplateArgument = $template->argumentTemplates->firstOrDefault(null, fn(ArgumentTemplate $t) => $t->name === $key);
			if ($matchingTemplateArgument !== null) {
				$arguments->add(Argument::fromTemplate($matchingTemplateArgument, $value));
			}
		}

		foreach ($argumentsMap->whereKey(fn($k) => is_numeric($k)) as $value) {
			$positionalArgumentTemplate = $template->argumentTemplates->firstOrDefault(null, fn(ArgumentTemplate $t) => $arguments->where(fn (Argument $a) => $a->name === $t->name)->isEmpty());
			if ($positionalArgumentTemplate === null) {
				break;
			}

			$arguments->add(Argument::fromTemplate($positionalArgumentTemplate, $value));
		}

		$missingArguments = $template->argumentTemplates->where(fn(ArgumentTemplate $t) => $arguments->where(fn(Argument $a) => $a->name === $t->name)->isEmpty())->toList();
		if (!empty($missingArguments)) {
			foreach ($missingArguments as $missingArgument) {
				if ($missingArgument->required) {
					throw new RequiredArgumentMissingException($missingArgument->name);
				}

				$arguments->add(Argument::fromTemplate($missingArgument, $missingArgument->default));
			}
		}

		return $arguments;
	}
}
