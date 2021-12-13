<?php
declare(strict_types=1);

namespace Elephox\Http;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
enum UploadError: int
{
	case UPLOAD_ERR_OK = 0;
	case UPLOAD_ERR_INI_SIZE = 1;
	case UPLOAD_ERR_FORM_SIZE = 2;
	case UPLOAD_ERR_PARTIAL = 3;
	case UPLOAD_ERR_NO_FILE = 4;
	case UPLOAD_ERR_NO_TMP_DIR = 6;
	case UPLOAD_ERR_CANT_WRITE = 7;
	case UPLOAD_ERR_EXTENSION = 8;

	public function getMessage(): string
	{
		return match ($this) {
			self::UPLOAD_ERR_OK => "There is no error, the file uploaded with success.",
			self::UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
			self::UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.",
			self::UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded.",
			self::UPLOAD_ERR_NO_FILE => "No file was uploaded.",
			self::UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder.",
			self::UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
			self::UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload."
		};
	}
}
