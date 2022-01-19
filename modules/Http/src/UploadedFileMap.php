<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;
use Elephox\Stream\ResourceStream;
use Elephox\Support\CustomMimeType;
use Elephox\Support\MimeType;

/**
 * @extends ArrayMap<string, Contract\UploadedFile>
 */
class UploadedFileMap extends ArrayMap implements Contract\UploadedFileMap
{
	public static function fromGlobals(?array $files = null): Contract\UploadedFileMap
	{
		$files ??= $_FILES;

		$map = new self();

		foreach ($files as $id => $file)
		{
			$clientFilename = $file['name'];
			$clientType = $file['type'];
			$size = $file['size'];
			$error = $file['error'];
			$tmpName = $file['tmp_name'];
			$fullPath = $file['full_path'];

			$mimeType = MimeType::tryFrom($clientType) ?? new CustomMimeType($clientType);
			$uploadError = UploadError::from($error);

			$uploadedFile = new UploadedFile($clientFilename, $fullPath, ResourceStream::fromFile($tmpName), $mimeType, $size, $uploadError);

			$map->put($id, $uploadedFile);
		}

		return $map;
	}
}
