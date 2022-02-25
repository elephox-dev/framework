<?php
declare(strict_types=1);

namespace Elephox\Configuration\Contract;

interface ConfigurationSection extends Configuration
{
	public function getKey(): string;
	public function getValue(): string|int|float|bool|null;
	public function setValue(string|int|float|bool|null $value): void;
	public function deleteValue(): void;
	public function getPath(): string;
}
