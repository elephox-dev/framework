<?php
declare(strict_types=1);

namespace Elephox\Support;

trait GetterSetterPrefixBuilder
{
	protected function buildGetterPrefixes(): iterable
	{
		yield 'get';
		yield 'is';
		yield 'has';
	}

	protected function buildSetterPrefixes(): iterable
	{
		yield 'set';
		yield 'put';
	}
}
