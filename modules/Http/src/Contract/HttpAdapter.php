<?php
declare(strict_types=1);

namespace Philly\Http\Contract;

interface HttpAdapter
{
	public function setUrl(string $url): HttpAdapter;

	public function setMethod(string $method): HttpAdapter;

	public function setHeaders(array $headers): HttpAdapter;

	public function setBody(?string $body): HttpAdapter;

	public function prepare(): HttpAdapter;

	public function send(): bool;

	public function getResponse(): ?string;

	public function getLastError(): ?string;

	public function cleanup(): void;
}
