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

	public function int(): int
	{
		if (!is_numeric($this->value)) {
			throw new ArgumentValidationException('Value cannot be converted to int: ' . get_debug_type($this->value));
		}

		return (int) $this->value;
	}

	public function float(): float
	{
		if (!is_numeric($this->value)) {
			throw new ArgumentValidationException('Value cannot be converted to float: ' . get_debug_type($this->value));
		}

		return (float) $this->value;
	}

	public function bool(): bool
	{
		if (is_string($this->value)) {
			return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
		}

		if (is_bool($this->value)) {
			return $this->value;
		}

		throw new ArgumentValidationException('Value cannot be converted to bool: ' . get_debug_type($this->value));
	}

	public function string(): string
	{
		if (
			is_string($this->value) ||
			is_numeric($this->value) ||
			is_bool($this->value)
		) {
			return (string) $this->value;
		}

		throw new ArgumentValidationException('Value cannot be converted to string: ' . get_debug_type($this->value));
	}

	public function array(): array
	{
		if (is_array($this->value)) {
			return $this->value;
		}

		return [$this->value];
	}

	public function nullableInt(): ?int
	{
		if ($this->value === null) {
			return null;
		}

		return $this->int();
	}

	public function nullableFloat(): ?float
	{
		if ($this->value === null) {
			return null;
		}

		return $this->float();
	}

	public function nullableBool(): ?bool
	{
		if ($this->value === null) {
			return null;
		}

		return $this->bool();
	}

	public function nullableString(): ?string
	{
		if ($this->value === null) {
			return null;
		}

		return $this->string();
	}

	public function nullableArray(): ?array
	{
		if ($this->value === null) {
			return null;
		}

		return $this->array();
	}
}
