<?php
declare(strict_types=1);

namespace Philly\Http;

use InvalidArgumentException;

enum UrlScheme: string {
	case HTTPS = 'https';
	case HTTP = 'http';
	case FTP = 'ftp';
	case FILE = 'file';
	case MAILTO = 'mailto';
	case SSH = 'ssh';

	public function getDefaultPort(): int
	{
		return match ($this) {
			self::HTTPS => 443,
			self::HTTP => 80,
			self::FTP => 21,
			self::SSH => 22,
			self::FILE,
			self::MAILTO => throw new InvalidArgumentException("No default port for $this->value scheme"),
		};
	}
}
