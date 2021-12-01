<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use DateTime;
use Elephox\Http\CookieSameSite;
use Stringable;

interface Cookie extends Stringable
{
	public function setName(string $name): void;

	public function getName(): string;

	public function setValue(?string $value): void;

	public function getValue(): ?string;

	public function setExpires(?DateTime $expires): void;

	public function getExpires(): ?DateTime;

	public function setPath(?string $path): void;

	public function getPath(): ?string;

	public function setDomain(?string $domain): void;

	public function getDomain(): ?string;

	public function setSecure(bool $secure): void;

	public function isSecure(): bool;

	public function setHttpOnly(bool $httpOnly): void;

	public function isHttpOnly(): bool;

	public function setSameSite(?CookieSameSite $sameSite): void;

	public function getSameSite(): ?CookieSameSite;

	public function setMaxAge(?int $maxAge): void;

	public function getMaxAge(): ?int;
}
