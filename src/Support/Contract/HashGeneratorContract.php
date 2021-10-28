<?php

namespace Philly\Base\Support\Contract;

interface HashGeneratorContract
{
    public function generateHash(object $object): string|int;
}
