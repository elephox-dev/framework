<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;
use Elephox\Files\File;
use Elephox\Support\CustomMimeType;
use Elephox\Mimey\MimeType;
use InvalidArgumentException;

/**
 * @extends ArrayMap<string, Contract\UploadedFile>
 */
class UploadedFileMap extends ArrayMap implements Contract\UploadedFileMap
{
	/**
	 * @param null|array<string, Contract\UploadedFile|array{name: string, type: string, size: int, error: int, tmp_name: string, full_path: string}> $files
	 */
	public static function fromGlobals(?array $files = null): Contract\UploadedFileMap
	{
		$files ??= $_FILES;

		$map = new self();

		foreach ($files as $id => $file) {
			if (is_array($file)) {
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
				$tmpFile = new File($fullPath);

				if ($size < 0) {
					$size = null;
				}

				/** @var int<0, max>|null $size */
				$uploadedFile = new UploadedFile($clientFilename, $fullPath, $tmpFile, $mimeType, $size, $uploadError);
			} elseif ($file instanceof Contract\UploadedFile) {
				$uploadedFile = $file;
			} else {
				throw new InvalidArgumentException("File values must be array or Contract\UploadedFile");
			}

			$map->put($id, $uploadedFile);
		}

		return $map;
	}
}
