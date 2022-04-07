<?php
declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()->in('modules/');
$config = new PhpCsFixer\Config();

return $config
	->setRules([
		'@PSR12' => true,
		'blank_line_after_opening_tag' => false,
		'strict_comparison' => true,
		'array_syntax' => ['syntax' => 'short'],
	])
	->setRiskyAllowed(true)
	->setFinder($finder)
;
