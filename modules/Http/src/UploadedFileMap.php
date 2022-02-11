<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;
use Elephox\Stream\ResourceStream;
use Elephox\Support\CustomMimeType;
use Mimey\MimeType;

/**
 * @extends ArrayMap<string, Contract\UploadedFile>
 */
class UploadedFileMap extends ArrayMap implements Contract\UploadedFileMap
{
	/**
	 * @param null|array<string, array{name: string, type: string, size: int, error: int, tmp_name: string, full_path: string}> $files
	 * @return Contract\UploadedFileMap
	 */
	public static function fromGlobals(?array $files = null): Contract\UploadedFileMap
	{
		$files ??= $_FILES;

		$map = new self();

		/**
		 * @var string $id
		 * @var array{name: string, type: string, size: int, error: int, tmp_name: string, full_path: string} $file
		 */
		foreach ($files as $id => $file)
		{
			$clientFilename = $file['name'];
			$clientType = $file['type'];
			$size = $file['size'];
			$error = $file['error'];
			$tmpName = $file['tmp_name'];
			$fullPath = $file['full_path'];

			$mimeType = MimeType::tryFrom($clientType);
			if ($mimeType === null && $clientType !== '') {
				$mimeType = new CustomMimeType($clientType);
			}

			$uploadError = UploadError::from($error);
			$resource = ResourceStream::fromFile($tmpName);

			$uploadedFile = new UploadedFile($clientFilename, $fullPath, $resource, $mimeType, $size > 0 ? $size : null, $uploadError);

			$map->put($id, $uploadedFile);
		}

		return $map;
	}
}
