<?php
declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()->in('modules/');
$config = new PhpCsFixer\Config();

return $config
	->setRules([
		'@PSR12' => true,
		'strict_comparison' => true,
		'array_syntax' => ['syntax' => 'short'],
	])
	->setFinder($finder)
;
