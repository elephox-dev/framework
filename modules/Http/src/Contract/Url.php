<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Support\Contract\StringConvertible;

interface Url extends StringConvertible
{
	public function getScheme(): ?string;

	public function getUsername(): ?string;

	public function getPassword(): ?string;

	public function getHost(): ?string;

	public function getPort(): ?int;

	public function getPath(): string;

	public function getQuery(): ?string;

	public function getFragment(): ?string;
}