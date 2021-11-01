<?php

namespace Philly\Base\Http\Contract;

use Philly\Base\Http\Method;

interface Request
{
    public function getUri(): string;

    public function getMethod(): Method;

    public function getHeaders(): ReadonlyHeaderMap;
}
