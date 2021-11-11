<?php
declare(strict_types=1);

namespace Elephox\Http;

enum UrlScheme: string {
	case HTTPS = 'https';
	case HTTP = 'http';
	case FTP = 'ftp';
	case FILE = 'file';
	case MAILTO = 'mailto';
	case SSH = 'ssh';
}
