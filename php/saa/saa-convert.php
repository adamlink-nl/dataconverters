<?php
declare(strict_types=1);

use Leones\AdamLinkR\Converter\Helper;
use Leones\AdamLinkR\Converter\SaaConverter;

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../bootstrap.php');

/**
 * Reads all xml-files in $dirIn and converts them to RDF turtle, for use in the AdamLink triplestore
 * Converts people, buildings and streets to AdamLink URI's
 * Converts types to AAT-URIS
 */
$dirIn = __DIR__ . '/../data/saa/bma';
$dirOut = __DIR__ . '/../data/saa/bma';


$itDir = new DirectoryIterator($dirIn);
$it = new RegexIterator($itDir, "/\.xml/i");

$what = 'record'; // record | image
Helper::writeln('Converting SAA to graph.');

/** @var SplFileInfo $fileinfo */
foreach ($it as $fileinfo) {

    Helper::writeln('Handling file ' . $fileinfo->getFilename());

    $outputFile = $dirOut . '/' . str_replace('.xml', '_' . $what . '.ttl', $fileinfo->getFilename());

    if ($what === 'image') {
        Helper::writeln('Adding images to graph.');
    } else {
        Helper::writeln('Converting SAA to graph.');
    }
    $converter = new SaaConverter(
        $fileinfo->getPathname(),
        $outputFile,
        $personMapper,
        $buildingMapper,
        $streetMapper,
        'turtle'
    );
    $converter->convert($what);
}
