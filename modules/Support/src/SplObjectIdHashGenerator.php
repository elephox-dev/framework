<?php
declare(strict_types=1);

namespace Philly\Support;

use Philly\Support\Contract\HasHash;
use Philly\Support\Contract\HashGenerator;

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
