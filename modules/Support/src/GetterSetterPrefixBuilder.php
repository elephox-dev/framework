<?php
declare(strict_types=1);

namespace Elephox\Support;

trait GetterSetterPrefixBuilder
{
	/**
	 * @return iterable<int, non-empty-string>
	 */
	protected function _buildGetterPrefixes(): iterable
	{
		yield 'get';
		yield 'is';
		yield 'has';
	}

	/**
	 * @return iterable<int, non-empty-string>
	 */
	protected function _buildSetterPrefixes(): iterable
	{
		yield 'set';
		yield 'put';
	}
}
