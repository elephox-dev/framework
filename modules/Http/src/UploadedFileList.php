<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayList;

/**
 * @extends ArrayList<Contract\UploadedFile>
 */
class UploadedFileList extends ArrayList implements Contract\UploadedFileList
{
}
