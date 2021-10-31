<?php

namespace Philly\Base\Support;

use Philly\Base\Support\Contract\HasHash;
use Philly\Base\Support\Contract\HashGenerator;

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
