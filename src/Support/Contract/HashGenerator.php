<?php

namespace Philly\Base\Support\Contract;

interface HashGenerator
{
    public function generateHash(object $object): string|int;
}
