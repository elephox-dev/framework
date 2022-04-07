<?php
declare(strict_types=1);

namespace Elephox\Http;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
enum UploadError: int
{
	case Ok = 0;
	case IniSize = 1;
	case FormSize = 2;
	case Partial = 3;
	case NoFile = 4;
	case NoTmpDir = 6;
	case CantWrite = 7;
	case Extension = 8;
	#[Pure]
	public function getMessage(): string
	{
		return match ($this) {
			self::Ok => 'There is no error, the file uploaded with success.',
			self::IniSize => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
			self::FormSize => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
			self::Partial => 'The uploaded file was only partially uploaded.',
			self::NoFile => 'No file was uploaded.',
			self::NoTmpDir => 'Missing a temporary folder.',
			self::CantWrite => 'Failed to write file to disk.',
			self::Extension => 'A PHP extension stopped the file upload.',
		};
	}
}
