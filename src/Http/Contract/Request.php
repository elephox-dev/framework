<?php

namespace Philly\Http\Contract;

use Philly\Http\Method;

interface Request
{
    public function getUri(): string;

    public function getMethod(): Method;

    public function getHeaders(): ReadonlyHeaderMap;
}
