<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\UrlScheme;
use Elephox\Support\Contract\ArrayConvertible;
use Stringable;

interface Url extends Stringable, ArrayConvertible
{
	public function getScheme(): ?string;

	public function getUrlScheme(): ?UrlScheme;

	public function getUsername(): ?string;

	public function getPassword(): ?string;

	public function getHost(): ?string;

	public function getPort(): ?int;

	public function getPath(): string;

	public function getQuery(): ?string;

	public function getFragment(): ?string;
}
