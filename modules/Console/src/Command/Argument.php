<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use LogicException;

/**
 * @property bool $hasDefault
 * @property null|list<string>|string|int|float|bool $default
 * @property null|string $description
 * @property string $name
 * @property null|Closure(list<string>|string|int|float|bool|null): (bool|string) $validator
 */
class Argument
{
	/**
	 * @param ArgumentTemplate $template
	 * @param list<string>|string|int|float|bool|null $value
	 *
	 * @return self
	 */
	public static function fromTemplate(ArgumentTemplate $template, null|array|string|int|float|bool $value): self
	{
		if ($template->validator !== null) {
			$validationResult = ($template->validator)($value);
			if ((is_bool($validationResult) && !$validationResult)) {
				throw new ArgumentValidationException("Validation failed for argument '$template->name'");
			}

			if (is_string($validationResult)) {
				throw new ArgumentValidationException($validationResult);
			}
		}

		return new self(
			$template,
			$value,
		);
	}

	public function __construct(
		public readonly ArgumentTemplate $template,
		public readonly null|array|string|int|float|bool $value,
	) {
	}

	public function __get(string $name): mixed
	{
		return $this->template->$name;
	}

	public function __set(string $name, mixed $value): void
	{
		throw new LogicException('Cannot set argument value');
	}

	public function __isset(string $name): bool
	{
		return isset($this->template->$name);
	}
}
