<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\GenericMap;

/**
 * @extends GenericMap<string, UploadedFile>
 */
interface UploadedFileMap extends GenericMap
{
	/**
	 * @param null|array<string, array{name: string, type: string, size: int, error: int, tmp_name: string, full_path: string}> $files
	 */
	public static function fromGlobals(?array $files = null): UploadedFileMap;
}
