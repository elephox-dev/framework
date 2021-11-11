<?php
declare(strict_types=1);

namespace Elephox\Support;

use Elephox\Support\Contract\HasHash;
use Elephox\Support\Contract\HashGenerator;

class SplObjectIdHashGenerator implements HashGenerator
{
	public function generateHash(object $object): string|int
	{
		if ($object instanceof HasHash) {
			return $object->getHash();
		}

		return spl_object_id($object);
	}
}
