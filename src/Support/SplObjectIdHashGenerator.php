<?php

namespace Philly\Base\Support;

use Philly\Base\Support\Contract\HasHash;
use Philly\Base\Support\Contract\HashGeneratorContract;

class SplObjectIdHashGenerator implements HashGeneratorContract
{
    public function generateHash(object $object): string|int
    {
        if ($object instanceof HasHash) {
            return $object->getHash();
        }

        return spl_object_id($object);
    }
}
