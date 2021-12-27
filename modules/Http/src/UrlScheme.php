<?php
declare(strict_types=1);

namespace Elephox\Http;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
enum UrlScheme: string implements Contract\UrlScheme
{
	case HTTPS = 'https';
	case HTTP = 'http';
	case FTP = 'ftp';
	case SFTP = 'sftp';
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
			self::SSH, self::SFTP => 22,
			self::MYSQL => 3306,
			default => null
		};
	}

	#[Pure] public function getScheme(): string
	{
		return $this->value;
	}
}
