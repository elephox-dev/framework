<?php
declare(strict_types=1);

namespace Elephox\Http;

use JetBrains\PhpStorm\Pure;

enum UrlScheme: string {
	case HTTPS = 'https';
	case HTTP = 'http';
	case FTP = 'ftp';
	case FILE = 'file';
	case MAILTO = 'mailto';
	case SSH = 'ssh';
	case MYSQL = 'mysql';

	#[Pure] public function getDefaultPort(): ?int
	{
		return match ($this) {
			self::HTTPS => 443,
			self::HTTP => 80,
			self::FTP => 21,
			self::SSH => 22,
			self::MYSQL => 3306,
			default => null
		};
	}
}
