<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\GenericMap;

/**
 * @extends GenericMap<string, UploadedFile>
 */
interface UploadedFileMap extends GenericMap
{
	public static function fromGlobals(?array $files = null): UploadedFileMap;
}
