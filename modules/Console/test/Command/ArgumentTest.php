<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use LogicException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Console\Command\Argument
 * @covers \Elephox\Console\Command\ArgumentTemplate
 * @covers \Elephox\Console\Command\ParameterTemplate
 *
 * @internal
 */
final class ArgumentTest extends TestCase
{
	public function testFromTemplateThrowsForFailingValidatorBool(): void
	{
		$template = new ArgumentTemplate("arg", validator: fn ($v) => false);

		$this->expectException(ArgumentValidationException::class);
		$this->expectExceptionMessage("Validation failed for argument 'arg'");

		Argument::fromTemplate($template, "val");
	}

	public function testFromTemplateThrowsForFailingValidatorMessage(): void
	{
		$template = new ArgumentTemplate("arg", validator: fn ($v) => 'failure message');

		$this->expectException(ArgumentValidationException::class);
		$this->expectExceptionMessage("failure message");

		Argument::fromTemplate($template, "val");
	}

	public function testGettersAreForwardedToTemplate(): void
	{
		$template = new ArgumentTemplate("arg", true, "default", "description", fn ($v) => $v === 'val');
		$arg = new Argument($template, "val");

		self::assertSame("arg", $arg->name);
		self::assertTrue($arg->hasDefault);
		self::assertSame("default", $arg->default);
		self::assertSame("description", $arg->description);
		self::assertNotNull($arg->validator);
	}

	public function testSetterThrows(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage("Cannot set argument value");

		$template = new ArgumentTemplate("arg");
		$arg = new Argument($template, "val");
		$arg->name = "no";
	}
}
