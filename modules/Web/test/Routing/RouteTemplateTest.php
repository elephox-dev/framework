<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Elephox\OOR\Regex;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Web\Routing\RouteTemplate
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Collection\Iterator\FlipIterator
 * @covers \Elephox\Collection\Iterator\SelectIterator
 * @covers \Elephox\OOR\Range
 * @covers \Elephox\Collection\IteratorProvider
 * @covers \Elephox\Collection\Iterator\EagerCachingIterator
 * @covers \Elephox\Collection\Iterator\KeySelectIterator
 * @covers \Elephox\Collection\Iterator\OrderedIterator
 * @covers \Elephox\Collection\OrderedEnumerable
 * @covers \Elephox\OOR\Regex
 * @covers \Elephox\Collection\DefaultEqualityComparer
 * @covers \Elephox\Web\Routing\InvalidRouteTemplateException
 *
 * @uses \Elephox\Collection\IsEnumerable
 * @uses \Elephox\Collection\IsKeyedEnumerable
 *
 * @internal
 */
class RouteTemplateTest extends TestCase
{
	public static function validRouteTemplatesNoParentProvider(): iterable
	{
		yield ['', '/', '#^/$#i', [], []];
		yield ['/', '/', '#^/$#i', [], []];
		yield ['//', '/', '#^/$#i', [], []];
		yield ['///', '/', '#^/$#i', [], []];
		yield ['abc', '/abc', '#^/abc$#i', [], []];
		yield ['/abc', '/abc', '#^/abc$#i', [], []];
		yield ['abc/', '/abc', '#^/abc$#i', [], []];
		yield ['/abc/', '/abc', '#^/abc$#i', [], []];
		yield ['/abc/{user}', '/abc/{user}', '#^/abc/(?<user>[^}/]+)$#i', ['user'], []];
		yield ['/a{user}b/', '/a{user}b', '#^/a(?<user>[^}/]+)b$#i', ['user'], []];
		yield ['/abc/{version:int}', '/abc/{version:int}', '#^/abc/(?<version>\d+)$#i', ['version:int'], []];
		yield ['/[controller]/poke', '/[controller]/poke', '#^/myController/poke$#i', [], ['controller']];
		yield ['[controller]/[action]', '/[controller]/[action]', '#^/myController/myAction$#i', [], ['controller', 'action']];
		yield ['/[controller]/[action]', '/[controller]/[action]', '#^/myController/myAction$#i', [], ['controller', 'action']];
		yield ['/[controller]/{slug}/draft/title', '/[controller]/{slug}/draft/title', '#^/myController/(?<slug>[^}/]+)/draft/title$#i', ['slug'], ['controller']];
	}

	/**
	 * @dataProvider validRouteTemplatesNoParentProvider
	 *
	 * @throws Exception
	 */
	public function testParseNoParent(string $template, string $normalized, string $regex, array $expectedVariables, array $expectedDynamics): void
	{
		$route = RouteTemplate::parse($template);

		static::assertSame($normalized, $route->getSource());
		static::assertSame($regex, $route->renderRegExp(['controller' => 'myController', 'action' => 'myAction']));

		$variables = $route->getVariableNames()->toList();
		$dynamics = $route->getDynamicNames()->toList();

		static::assertSame($expectedVariables, $variables);
		static::assertSame($expectedDynamics, $dynamics);
	}

	public static function strayClosingBracketRouteTemplatesProvider(): iterable
	{
		yield [']'];
		yield ['/]'];
		yield ['/]/'];
		yield [']/'];
		yield ['}'];
		yield ['/}'];
		yield ['/}/'];
		yield ['}/'];
	}

	/**
	 * @dataProvider strayClosingBracketRouteTemplatesProvider
	 */
	public function testParseThrowsForStrayClosingBracket(string $template): void
	{
		$this->expectException(InvalidRouteTemplateException::class);
		$this->expectExceptionMessage('Invalid route template: stray closing bracket');

		RouteTemplate::parse($template);
	}

	public static function invalidVariableNameProvider(): iterable
	{
		yield ['{/}'];
		yield ['{]}'];
		yield ['{[}'];
	}

	/**
	 * @dataProvider invalidVariableNameProvider
	 */
	public function testParseThrowsForInvalidVariableName(string $template): void
	{
		$this->expectException(InvalidRouteTemplateException::class);
		$this->expectExceptionMessage('Invalid route template: invalid character in variable name');

		RouteTemplate::parse($template);
	}

	public static function emptyVariableNameProvider(): iterable
	{
		yield ['{}'];
		yield ['/{}'];
		yield ['/{}/'];
		yield ['{}/'];
	}

	/**
	 * @dataProvider emptyVariableNameProvider
	 */
	public function testParseThrowsForEmptyVariableName(string $template): void
	{
		$this->expectException(InvalidRouteTemplateException::class);
		$this->expectExceptionMessage('Invalid route template: empty variable name');

		RouteTemplate::parse($template);
	}

	public static function invalidDynamicsNameProvider(): iterable
	{
		yield ['[/]'];
		yield ['[}]'];
		yield ['[{]'];
	}

	/**
	 * @dataProvider invalidDynamicsNameProvider
	 */
	public function testParseThrowsForInvalidDynamicsName(string $template): void
	{
		$this->expectException(InvalidRouteTemplateException::class);
		$this->expectExceptionMessage('Invalid route template: invalid character in dynamics name');

		RouteTemplate::parse($template);
	}

	public static function emptyDynamicsNameProvider(): iterable
	{
		yield ['[]'];
		yield ['/[]'];
		yield ['/[]/'];
		yield ['[]/'];
	}

	/**
	 * @dataProvider emptyDynamicsNameProvider
	 */
	public function testParseThrowsForEmptyDynamicsName(string $template): void
	{
		$this->expectException(InvalidRouteTemplateException::class);
		$this->expectExceptionMessage('Invalid route template: empty dynamics name');

		RouteTemplate::parse($template);
	}

	public static function routeMatchesProvider(): iterable
	{
		yield ['/', '/', true];
		yield ['/abc', '/abc', true];
		yield ['/abc', '/ABC', true];
		yield ['/abc', '/abcd', false];
		yield ['/abc', '/', false];
		yield ['/[controller]', '/articles', true];
		yield ['/[controller]/{slug}/draft/title', '/articles/2023-12-01-wip/draft/title', true];
		yield ['/[controller]/{slug}/draft/title', '/articles/2023-12-01-wip/draft/', false];
		yield ['/[controller]/{slug}/draft/title', '/articles/2023-12-01-wip/draft', false];
		yield ['/[controller]/{slug}/draft/title', '/articles/2023-12-01-wip/title/draft', false];
	}

	/**
	 * @dataProvider routeMatchesProvider
	 */
	public function testRouteTemplateRegexMatchesRoutes(string $template, string $route, bool $shouldMatch): void
	{
		$parsed = RouteTemplate::parse($template);

		static::assertSame($shouldMatch, Regex::matches($parsed->renderRegExp(['controller' => 'articles']), $route));
	}

	public static function routeTemplateWthParentProvider(): iterable
	{
		yield ['', '', '/'];
		yield ['', '/', '/'];
		yield ['/', '', '/'];
		yield ['/', '/', '/'];
		yield ['controller', '', '/controller'];
		yield ['/controller', '', '/controller'];
		yield ['controller/', '', '/controller'];
		yield ['/controller/', '', '/controller'];
		yield ['', 'action', '/action'];
		yield ['', '/action', '/action'];
		yield ['', 'action/', '/action'];
		yield ['', '/action/', '/action'];
		yield ['controller', 'action', '/controller/action'];
		yield ['controller/', 'action', '/controller/action'];
		yield ['/controller', 'action', '/controller/action'];
		yield ['/controller/', 'action', '/controller/action'];
		yield ['controller', '/action', '/controller/action'];
		yield ['controller', 'action/', '/controller/action'];
		yield ['controller', '/action/', '/controller/action'];
		yield ['/controller/', '/action/', '/controller/action'];
		yield ['controller/', '/action', '/controller/action'];
	}

	/**
	 * @dataProvider routeTemplateWthParentProvider
	 */
	public function testRouteTemplateWithParentNormalizesTemplate(string $parent, string $route, string $expectedRoute): void
	{
		$parentTemplate = RouteTemplate::parse($parent);
		$routeTemplate = RouteTemplate::parse($route, $parentTemplate);

		static::assertSame($expectedRoute, $routeTemplate->getSource());
	}
}
