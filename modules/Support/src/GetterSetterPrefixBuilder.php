<?php
declare(strict_types=1);

namespace Elephox\Support;

trait GetterSetterPrefixBuilder
{
	/**
	 * @return iterable<int, non-empty-string>
	 */
	protected function buildGetterPrefixes(): iterable
	{
		yield 'get';
		yield 'is';
		yield 'has';
	}

	/**
	 * @return iterable<int, non-empty-string>
	 */
	protected function buildSetterPrefixes(): iterable
	{
		yield 'set';
		yield 'put';
	}
}
