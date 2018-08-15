<?php
declare(strict_types=1);

namespace Leones\AdamLinkR\Converter;

use EasyRdf\Graph;
use EasyRdf\RdfNamespace;
use EasyRdf\Resource;
use Leones\AdamLinkR\Domain\SaaGeographicalName;
use Leones\AdamLinkR\Mapper\BuildingNameToAdamLinkBuildingMapper;
use Leones\AdamLinkR\Mapper\PersonToAdamLinkPersonMapper;
use Leones\AdamLinkR\Mapper\StreetNameToAdamLinkUriMapper;
use Leones\AdamLinkR\Mapper\StringToAATMapper;


final class SaaConverter
{
    const BATCH_SIZE = 400;
    const DEV_BATCH  = 40;

    const ROOT_ELEMENT = 'rdf:Description';

    /** @var \XMLReader */
    private $reader;

    private $fileToWrite;
    private $format;

    /** @var PersonToAdamLinkPersonMapper */
    private $personMapper;

    /** @var BuildingNameToAdamLinkBuildingMapper */
    private $buildingMapper;

    /** @var StreetNameToAdamLinkUriMapper */
    private $streetMapper;

    public function __construct(
        string $xmlFile,
        string $fileToWrite,
        string $format
    ) {
        if (! file_exists($xmlFile)) {
            throw new \Exception(sprintf('File "%s" does not exist', $xmlFile));
        }
        $this->reader = new \XMLReader();
        $this->reader->open($xmlFile);

        if (file_exists($fileToWrite)) {
            unlink($fileToWrite);
        }

        $this->fileToWrite = $fileToWrite;
        $this->format = $format;

        $this->personMapper = new PersonToAdamLinkPersonMapper();
        $this->buildingMapper = new BuildingNameToAdamLinkBuildingMapper();
        $this->streetMapper = new StreetNameToAdamLinkUriMapper();
    }

    public function convert(string $mode = 'record')
    {
        $records = [];
        $batchCount = 0;
        $totalCount = 0;

        while ($this->reader->read()) {
            if ($this->reader->nodeType === \XMLReader::ELEMENT && $this->reader->name == self::ROOT_ELEMENT) {
                $xml = new \SimpleXMLElement($this->reader->readOuterXML());

                $records[] = $xml;
                $batchCount++;
                $totalCount++;

                // handle the batch and reset the counter.
                if ($batchCount >= self::BATCH_SIZE) {
                    Helper::writeln('Total count: ' . $totalCount);
                    $this->processBatch($records, $mode);
                    $batchCount = 0;
                    $records = [];
                    $graph = null;
                }

            }

        }

        $this->reader->close();

        // handle the leftovers from last batch
        $this->processBatch($records, $mode);
        $graph = null;

        Helper::writeln("Handled $totalCount records.");
    }

    private function processBatch(array $records, string $mode)
    {
        $graph = $this->initGraph();

        if ($mode === 'image') {
            foreach ($records as $row) {
                $graph = $this->addImagesToGraph($graph, $row);
            }
        } else {
            foreach ($records as $row) {
                $graph = $this->addToGraph($graph, $row);
            }
        }

        Helper::writeln('Flushing batch of ' . count($records) . ' to file.');
        file_put_contents(
            $this->fileToWrite,
            $graph->serialise($this->format),
            FILE_APPEND | LOCK_EX
        );
    }

    public function addImagesToGraph(Graph $graph, \SimpleXMLElement $xml): Graph
    {
        $ns = $xml->getNamespaces(true);

        $id = Helper::cleanUpString((string)$xml->children($ns['dc'])->identifier);

        /** @var Resource $record */
        $record = $graph->resource($this->uri($id), 'edm:ProvidedCHO');

        $this->handleImages($record, $id);

        return $graph;
    }

    public function addToGraph(Graph $graph, \SimpleXMLElement $xml): Graph
    {
        $ns = $xml->getNamespaces(true);

        $id = Helper::cleanUpString((string)$xml->children($ns['dc'])->identifier);

        /** @var Resource $record */
        $record = $graph->resource($this->uri($id), 'edm:ProvidedCHO');

        // simple dc elements
        $record->set('dc:identifier', preg_replace("/\r|\n/", '', $id));
        $record->set('dc:title', (string)$xml->children($ns['dc'])->title);


        StringToAATMapper::convertDcTypeToUri($record, (string)$xml->children($ns['dc'])->type);

        $record->set('dc:description',
            Helper::fixQuotesInLiteralString((string)$xml->children($ns['dc'])->description));

        $this->handleCreators($record, $xml);
        //$this->handleSubjects($record, $xml);

        $record->set('dct:provenance', (string)$xml->children($ns['dc'])->provenance);
        $graph->add($record, 'dc:source', Helper::cleanUpString((string)$xml->children($ns['dc'])->source));

        // tod write handleRights
        $dcRights = (string)$xml->children($ns['dc'])->rights;
        $record->set('dc:rights', $dcRights);

        // todo keep rights restrictions in place
        if ($dcRights === 'Auteursrechtvrij') {
            $record->addResource(
                'dcterms:rightsStatement',
                'https://creativecommons.org/publicdomain/zero/1.0/'
            );
        }
        $record->addResource('void:inDataset', '<https://data.adamlink.nl/saa/beeldbank/>');

        $this->handleDates($record, $xml);

        return $graph;
    }

    public function handleImages(Resource $record, string $identifier): Resource
    {
        $image = $this->fetchImageThroughOpenSearchApi($identifier);
        print '.';
        if (strlen($image) > 1) {
            $record->addResource('foaf:depiction', $image);
        }

        return $record;
    }


    private function handleSubjects(Resource $record, \SimpleXMLElement $xml): Resource
    {
        // extract all subjects, and supply the handleSubject with correct type
        // but also pass along entire $xml, because we need to extract data form other elements (sk:parameter)
        $subjects = $xml->xpath('dc:subject');

        if (count($subjects) > 0) {
            foreach ($subjects as $subject) {
                $this->handleSubject($record, $xml, (string) $subject->attributes()->{'name'});
            }
        }

        return $record;
    }

    /**
     * Can contain either geportretteerde|geografische naam|gebouw
     * If we do not have a type we default to a simple string and will not try to map anything
     */
    private function handleSubject(Resource $record, \SimpleXMLElement $xml, string $type): Resource
    {
        switch ($type) {
            case 'geportretteerde':
                $this->handleDepictedPeople($record, $xml);
                break;
            case 'gebouw':
                // the property that gets filled is dct:spatial
                $this->handleBuildings($record, $xml);
                break;
            case 'geografische naam':
                $this->handleStreets($record, $xml);
                break;
            default:
                $ns = $xml->getNamespaces(true);
                $record->set('dc:subject', (string)$xml->children($ns['dc'])->subject);
                break;
        }

        return $record;
    }

    private function handleDepictedPeople(Resource $record, \SimpleXMLElement $xml): Resource
    {
        $persons = $this->extractDepictedPeopleFromDcSubject($xml);

        if (count($persons) > 0) {
            foreach ($persons as $person) {
                $cleanCreator = Helper::stripStuffBetweenBrackets($person);

                // try clean name first
                $uriFound = $this->personMapper->map($cleanCreator);

                if (strlen($uriFound) < 1) {
                    $uriFound = $this->personMapper->map($person);
                }

                if (strlen($uriFound) > 1) {
                    $record->addResource('dc:subject', $uriFound);
                } else {
                    $record->add('dc:subject', (string)$cleanCreator);
                }
            }
        }

        return $record;
    }

    private function handleCreators(Resource $record, \SimpleXMLElement $xml): Resource
    {
        $creators = $this->extractCreators($xml);

        if (count($creators) > 0) {
            foreach ($creators as $creator) {
                $cleanCreator = Helper::stripStuffBetweenBrackets($creator);

                // try clean name first
                $uriFound = $this->personMapper->map($cleanCreator);

                if (strlen($uriFound) < 1) {
                    $uriFound = $this->personMapper->map($creator);
                }

                if (strlen($uriFound) > 1) {
                    $record->addResource('dc:creator', $uriFound);
                } else {
                    $record->add('dc:creator', (string)$cleanCreator);
                }
            }
        }

        return $record;
    }

    private function handleBuildings(Resource $record, \SimpleXMLElement $xml): Resource
    {
        $buildings = $this->extractBuildingsFromParameters($xml);

        if (count($buildings) > 0) {
            foreach ($buildings as $building) {
                $uri = $this->buildingMapper->map($building);

                if (strlen($uri) > 1) {
                    $record->addResource('dct:spatial', $uri);
                } else {
                    $record->add('dct:spatial', (string)$building);
                }
            }
        }

        return $record;
    }

    /**
     * Enkele opties voor dc:subject in combinatie met streets
     *
     * dc:subject name="geografische naam
     * dc:subject name="geportretteerde
     * dc:subject name="gebouw
     *
     * @param Resource $record
     * @param \SimpleXMLElement $xml
     * @return Resource
     */
    private function handleStreets(Resource $record, \SimpleXMLElement $xml): Resource
    {
        $streets = $this->extractGeographicalNames($xml);
        //$this->log(count($streets) . ' streets found');

        if (count($streets) > 0) {
            foreach ($streets as $street) {
                /* @var \Leones\AdamLinkR\Domain\SaaGeographicalName $street */
                $uri = $this->streetMapper->map($street->name());

                if (strlen($uri) > 1) {
                    $record->addResource('dct:spatial', $uri);
                } else {
                    $record->add('dct:spatial', $street->name());
                }
            }
        }

        return $record;
    }

    private function handleDates(Resource $record, \SimpleXMLElement $xml): Resource
    {
        $dates = $this->extractDatesFromParameters($xml);

        if (! empty($dates && ($dates[0] instanceof \DateTime))) {
            $startDate = \EasyRdf\Literal\Date::create($dates[0]->format('Y-m-d'), '', 'xsd:date');
            $record->set('sem:hasBeginTimeStamp', $startDate);
        }
        if (! empty($dates && ($dates[1] instanceof \DateTime))) {
            $endDate = \EasyRdf\Literal\Date::create($dates[1]->format('Y-m-d'), '', 'xsd:date');
            $record->set('sem:hasEndTimeStamp', $endDate);
        }

        return $record;
    }

    private function extractGeographicalNames(\SimpleXMLElement $xml): array
    {
        $elements = $xml->xpath('sk:parameter[@name="geografische naam"]');
        $geoNames = [];
        foreach ($elements as $element) {
            $geoNames[] = SaaGeographicalName::fromXml($element);
        }

        return $geoNames;
    }

    private function extractBuildingsFromParameters(\SimpleXMLElement $xml): array
    {
        $buildingElements = $xml->xpath('sk:parameter[@name="gebouw"]');
        $buildings = [];
        foreach ($buildingElements as $building) {
            $buildings[] = (string)$building;
        }

        return $buildings;
    }

    private function extractDepictedPeopleFromDcSubject(\SimpleXMLElement $xml): array
    {
        $personElements = $xml->xpath('dc:subject[@name="geportretteerde"]');
        $persons = [];
        foreach ($personElements as $person) {
            $persons[] = (string)$person;
        }

        return $persons;
    }

    private function extractDatesFromParameters(\SimpleXMLElement $xml): array
    {
        $dateElements = $xml->xpath('sk:parameter[@name="datering"]');
        if (count($dateElements) > 0) {
            $dateString = (string)$dateElements[0];
            list ($start, $end) = explode('-', $dateString);

            return [
                \DateTime::createFromFormat('Ymd', $start),
                \DateTime::createFromFormat('Ymd', $end)
            ];
        }

        return [];
    }

    private function extractCreators(\SimpleXMLElement $xml): array
    {
        $creatorElements = $xml->xpath('dc:creator');
        $persons = [];
        foreach ($creatorElements as $person) {
            $persons[] = (string)$person;
        }

        return $persons;
    }

    private function initGraph(): Graph
    {
        RdfNamespace::set('edm', 'http://www.europeana.eu/schemas/edm/');
        RdfNamespace::set('dc', 'http://purl.org/dc/elements/1.1/');
        RdfNamespace::set('dct', 'http://purl.org/dc/terms/');
        RdfNamespace::set('sem', 'http://semanticweb.cs.vu.nl/2009/11/sem/');
        RdfNamespace::set('xsd', 'http://www.w3.org/2001/XMLSchema#');
        RdfNamespace::set('void', 'http://rdfs.org/ns/void#');

        return new Graph();
    }

    private function fetchImageThroughOpenSearchApi(string $identifier): string
    {
        $file = sprintf(
            'http://beeldbank.amsterdam.nl/api/opensearch/?searchTerms=dc_identifier:%s',
            $identifier
        );
        $xml = simplexml_load_file($file);
        $image = (string)$xml->channel->item->enclosure['url'];

        if (!strpos($image, '/.jpg') && $image !== '') {
            return str_replace('140x140', '640x480', $image);
        }

        return '';
    }

    private function uri(string $id): string
    {
        return 'http://beeldbank.amsterdam.nl/afbeelding/' . $id;
    }

}

