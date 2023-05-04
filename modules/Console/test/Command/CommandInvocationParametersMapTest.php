<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use JsonException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Console\Command\CommandInvocationParametersMap
 * @covers \Elephox\Collection\ArrayMap
 *
 * @uses \Elephox\Collection\IsKeyedEnumerable
 *
 * @internal
 */
final class CommandInvocationParametersMapTest extends TestCase
{
	public function validFromCommandLineData(): iterable
	{
		yield ['', []];
		yield [' ', []];
		yield ['arg1 arg2 ', ['arg1', 'arg2']];
		yield ["non-quoted-arg 'quoted arg'", ['non-quoted-arg', 'quoted arg']];
		yield ['"quoted arg" non-quoted-arg', ['quoted arg', 'non-quoted-arg']];
		yield ['-a', ['a' => true]];
		yield ['/a', ['a' => true]];
		yield ['-rr', ['r' => 2]];
		yield ['-rrr', ['r' => 3]];
		yield ['--long', ['long' => true]];
		yield ['/long', ['long' => true]];
		yield ['---', ['-' => true]];
		yield ['--long-with-value=value', ['long-with-value' => 'value']];
		yield ["--long-with-value='quoted'", ['long-with-value' => 'quoted']];
		yield ['/slash-value=yes', ['slash-value' => 'yes']];
		yield ['-e=', ['e' => null]];
		yield ['--empty=', ['empty' => null]];
		yield ['-a=3 -a=5 -a=6', ['a' => ['3', '5', '6']]];
		yield ["--duplicate=a --duplicate='b' --duplicate=\"c\"", ['duplicate' => ['a', 'b', 'c']]];
		yield ["arg -sv --long --val='string'", [0 => 'arg', 's' => true, 'v' => true, 'long' => true, 'val' => 'string']];
	}

	/**
	 * @dataProvider validFromCommandLineData
	 *
	 * @throws JsonException
	 */
	public function testValidFromCommandLine(string $commandLine, array $arrayMap): void
	{
		$map = CommandInvocationParametersMap::fromCommandLine($commandLine);

		self::assertSame($arrayMap, $map->toArray());
	}

	public function incompleteFromCommandLineData(): iterable
	{
		yield ['-', 'Expected short option identifier'];
		yield ['- ', 'Expected short option identifier'];
		yield ['--', 'Expected long option identifier'];
		yield ["'", "Expected second quote (') to end quoted argument"];
		yield ['"', 'Expected second quote (") to end quoted argument'];
		yield ["-s='", "Expected second quote (') to end quoted argument"];
		yield ['--long="', 'Expected second quote (") to end quoted argument'];
	}

	/**
	 * @dataProvider incompleteFromCommandLineData
	 *
	 * @throws JsonException
	 */
	public function testIncompleteFromCommandLine(string $commandLine, string $message): void
	{
		$this->expectException(IncompleteCommandLineException::class);
		$this->expectExceptionMessage($message);

		CommandInvocationParametersMap::fromCommandLine($commandLine);
	}

	public function invalidFromCommandLineData(): iterable
	{
		yield ["--'", "Invalid option name character: '''"];
		yield ['--"', "Invalid option name character: '\"'"];
		yield ['-- -s', "Invalid option name character: ' '"];
		yield ['--=', "Invalid option name character: '='"];
		yield ["--sad'", "Invalid option name character: '''"];
		yield ["-a=''b", "Additional character after quoted argument: 'b'"];
	}

	/**
	 * @dataProvider invalidFromCommandLineData
	 *
	 * @throws JsonException
	 */
	public function testInvalidFromCommandLine(string $commandLine, string $message): void
	{
		$this->expectException(InvalidCommandLineException::class);
		$this->expectExceptionMessage($message);

		CommandInvocationParametersMap::fromCommandLine($commandLine);
	}

	public function erroringValidFromCommandLineData(): iterable
	{
		yield ['-k=v -k', 'Option "k" was already defined with value "v". Repeated option reset this to "true"', ['k' => true]];
		yield ['--key=value --key', 'Option "key" was already defined with value "value". Repeated option reset this to "true"', ['key' => true]];
		yield ['--key=value --key --key', 'Option "key" was already defined with value "value". Repeated option reset this to "true"', ['key' => true]];
	}

	/**
	 * @dataProvider erroringValidFromCommandLineData
	 *
	 * @throws JsonException
	 */
	public function testErroringValidCommandLine(string $commandLine, string $error, array $arrayMap): void
	{
		set_error_handler(static function (int $errno, string $errstr) use ($error): void {
			self::assertSame($error, $errstr);
		}, E_USER_WARNING);

		$map = CommandInvocationParametersMap::fromCommandLine($commandLine);

		restore_error_handler();

		self::assertSame($arrayMap, $map->toArray());
	}
}
