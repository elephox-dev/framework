<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\GenericList;

/**
 * @extends GenericList<UploadedFile>
 */
interface UploadedFileList extends GenericList
{
}
