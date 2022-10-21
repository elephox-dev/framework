<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;
use Elephox\Files\File;
use Elephox\Support\CustomMimeType;
use Elephox\Mimey\MimeType;

/**
 * @extends ArrayMap<string, Contract\UploadedFile>
 */
class UploadedFileMap extends ArrayMap implements Contract\UploadedFileMap
{
	/**
	 * @param null|array<string, array{name: string, type: string, size: int, error: int, tmp_name: string, full_path: string}> $files
	 */
	public static function fromGlobals(?array $files = null): Contract\UploadedFileMap
	{
		$files ??= $_FILES;

		$map = new self();

		/**
		 * @var array{name: string, type: string, size: int, error: int, tmp_name: string, full_path: string} $file
		 */
		foreach ($files as $id => $file) {
			$clientFilename = $file['name'];
			$clientType = $file['type'];
			$size = $file['size'];
			$error = $file['error'];
			$fullPath = $file['full_path'];

			$mimeType = MimeType::tryFrom($clientType);
			if ($mimeType === null && $clientType !== '') {
				$extension = '';
				if (!empty($fullPath)) {
					$extension = pathinfo($fullPath, PATHINFO_EXTENSION);
				}

				$mimeType = CustomMimeType::from($clientType, $extension);
			}

			$uploadError = UploadError::from($error);
			$resource = File::openStream($fullPath);

			if ($size < 0) {
				$size = null;
			}

			/** @var int<0, max>|null $size */
			$uploadedFile = new UploadedFile($clientFilename, $fullPath, $resource, $mimeType, $size, $uploadError);

			$map->put($id, $uploadedFile);
		}

		return $map;
	}
}
