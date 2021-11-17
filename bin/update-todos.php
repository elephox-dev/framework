<?php
declare(strict_types=1);

/**
 * @param string $dir
 * @param string[] $results
 * @return string[]
 */
function gatherSourceFiles(string $dir, array &$results = []): array
{
	$files = scandir($dir);
	if (!$files) {
		return $results;
	}

	foreach ($files as $value) {
		$path = realpath($dir . DIRECTORY_SEPARATOR . $value);
		if (is_file($path)) {
			if (!str_ends_with($path, '.php')) {
				continue;
			}

			$results[] = $path;
		} else if ($value !== "." && $value !== ".." && !str_ends_with($path, "vendor") && is_dir($path)) {
			gatherSourceFiles($path, $results);
		}
	}

	return $results;
}

$root = dirname(__DIR__) . DIRECTORY_SEPARATOR;
$src = $root . 'modules' . DIRECTORY_SEPARATOR;
$readmeFile = $root . 'README.md';

echo "Gathering files...\n";

$sourceFiles = gatherSourceFiles($src);
$pattern = '/\N*(TODO|FIXME|MAYBE|IDEA):?\s*(\N*)/i';

echo "Processing " . count($sourceFiles) . " files...\n";
$matches = [];
foreach ($sourceFiles as $sourceFile) {
	$contents = file_get_contents($sourceFile);
	preg_match_all($pattern, $contents, $fileMatches);
	if (empty($fileMatches[0])) {
		continue;
	}

	foreach ($fileMatches[1] as $i => $file) {
		$matches[$file] = $matches[$file] ?? [];
		$matches[$file][$sourceFile] = $matches[$file][$sourceFile] ?? [];
		$matches[$file][$sourceFile][] = $fileMatches[2][$i];
	}
}

$readmeContents = file_get_contents($readmeFile);
$todos = "<!-- start todos -->\n## TODOs Found:\n\n";
echo count($matches) . " categories found.\n";
foreach ($matches as $category => $files) {
	$todos .= "### $category\n\n";
	echo "Category $category contains " . count($files) . " files.\n";
	foreach ($files as $file => $entries) {
		$file = str_replace($src, '', $file);
		$todos .= "- [ ] $file\n";
		foreach ($entries as $entry) {
			$todos .= "  - [ ] $entry\n";
		}
	}
	$todos .= "\n";
}
$todos .= "<!-- end todos -->";
$readmeContents = preg_replace('/<!-- start todos -->.*<!-- end todos -->/s', $todos, $readmeContents);

echo "Writing to $readmeFile...\n";
file_put_contents($readmeFile, $readmeContents);

echo "Done.\n";
