<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\GenericMap;
use Psr\Http\Message\UploadedFileInterface;

/**
 * @extends GenericMap<array-key, UploadedFileInterface>
 */
interface UploadedFileMap extends GenericMap
{
	/**
	 * @param null|array<array-key, array{name: string, type: string, size: int, error: int, tmp_name: string, full_path: string}> $files
	 */
	public static function fromGlobals(?array $files = null): self;
}
