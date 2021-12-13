<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Support\Contract\MimeType as MimeTypeContract;
use JetBrains\PhpStorm\Pure;

interface Response extends Message
{
	public function getHeaderMap(): ResponseHeaderMap;

	#[Pure] public function getResponseCode(): ResponseCode;

	public function withResponseCode(ResponseCode $code): static;

	#[Pure] public function getContentType(): ?MimeTypeContract;

	public function withContentType(?MimeTypeContract $mimeType): static;

	public function send(): void;
}
