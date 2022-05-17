<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use LogicException;

/**
 * @property string $name
 * @property null|string $short
 * @property bool $hasValue
 * @property null|list<string>|string|int|float|bool $default
 * @property null|string $description
 * @property null|Closure $validator
 */
class Option
{
	/**
	 * @param OptionTemplate $template
	 * @param list<string>|string|int|float|bool|null $value
	 *
	 * @return self
	 */
	public static function fromTemplate(OptionTemplate $template, null|array|string|int|float|bool $value): self
	{
		if ($template->validator !== null) {
			$validationResult = ($template->validator)($value);
			if ((is_bool($validationResult) && !$validationResult)) {
				throw new OptionValidationException("Validation failed for option '$template->name'");
			}

			if (is_string($validationResult)) {
				throw new OptionValidationException($validationResult);
			}
		}

		return new self(
			$template,
			$value,
		);
	}

	/**
	 * @param OptionTemplate $template
	 * @param list<string>|string|int|float|bool|null $value
	 */
	public function __construct(
		public readonly OptionTemplate $template,
		public readonly null|array|string|int|float|bool $value,
	) {
	}

	public function __get(string $name): mixed
	{
		return $this->template->$name;
	}

	public function __set(string $name, mixed $value): void
	{
		throw new LogicException('Cannot set option value');
	}

	public function __isset(string $name): bool
	{
		return isset($this->template->$name);
	}
}
