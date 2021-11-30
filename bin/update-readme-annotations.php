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
$todoPattern = '/\N*(TODO|FIXME|MAYBE|IDEA):?\s*(\N*)/';
$issuePattern = '/(?:(?<repo>\w+\/\w+)#(?<issue>\d+))/';

echo "Processing " . count($sourceFiles) . " files...\n";
$matches = [];
$issues = [];
foreach ($sourceFiles as $sourceFile) {
	$contents = file_get_contents($sourceFile);

	preg_match_all($todoPattern, $contents, $fileMatches);
	if (!empty($fileMatches[0])) {
		foreach ($fileMatches[1] as $i => $matchedCategory) {
			$matches[$matchedCategory] ??= [];
			$matches[$matchedCategory][$sourceFile] ??= [];
			$matches[$matchedCategory][$sourceFile][] = $fileMatches[2][$i];
		}
	}

	preg_match_all($issuePattern, $contents, $issueMatches);
	if (empty($issueMatches['repo'])) {
		continue;
	}

	foreach ($issueMatches['repo'] as $i => $repo) {
		$issues[$repo] = $issues[$repo] ?? [];
		$issues[$repo][] = $issueMatches['issue'][$i];
		$issues[$repo] = array_unique($issues[$repo]);
	}
}

$emojiMap = [
	'FIXME' => '⚠️',
	'TODO' => '✅',
	'MAYBE' => '🤔',
	'IDEA' => '💡',
];
$readmeContents = file_get_contents($readmeFile);
$annotations = "<!-- start annotations -->\n\n## 📋 Source code annotations\n\n";
echo count($matches) . " categories found.\n";
foreach ($matches as $category => $files) {
	$annotations .= "### $emojiMap[$category] $category\n\n";
	echo "Category $category contains " . count($files) . " files.\n";
	foreach ($files as $matchedCategory => $entries) {
		$matchedCategory = str_replace($src, '', $matchedCategory);
		$annotations .= "- [ ] [$matchedCategory](https://github.com/elephox-dev/framework/tree/main/modules/$matchedCategory)\n";
		foreach ($entries as $entry) {
			$annotations .= "  - [ ] $entry\n";
		}
	}
	$annotations .= "\n";
}

$annotations .= "\n### 🚧 Open issues from other repositories\n\n";
foreach ($issues as $repo => $issueNumbers) {
	$annotations .= "- [$repo](https://github.com/$repo)\n";
	foreach ($issueNumbers as $issueNumber) {
		$annotations .= "  - [#$issueNumber](https://github.com/$repo/issues/$issueNumber)\n";
	}
	$annotations .= "\n";
}
$annotations .= "<!-- end annotations -->";
$readmeContents = preg_replace('/<!-- start annotations -->.*<!-- end annotations -->/s', $annotations, $readmeContents);

echo "Writing to $readmeFile...\n";
file_put_contents($readmeFile, $readmeContents);

echo "Done.\n";
