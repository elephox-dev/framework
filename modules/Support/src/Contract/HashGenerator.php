<?php

namespace Philly\Support\Contract;

interface HashGenerator
{
	public function generateHash(object $object): string|int;
}
