<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Contract;

use ArrayAccess;
use Elephox\Collection\Contract\GenericMap;
use Elephox\Http\Url;

interface MatchedUrlTemplate extends GenericMap, ArrayAccess
{
	public function getTemplateSource(): string;

	public function getUrl(): Url;
}
