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

	#[Pure] public function usesTrimmedPath(): bool
	{
		return match ($this) {
			self::MYSQL => true,
			default => false
		};
	}
}
