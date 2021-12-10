<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

interface UrlScheme
{
	public function getScheme(): string;

	public function getDefaultPort(): ?int;
}
