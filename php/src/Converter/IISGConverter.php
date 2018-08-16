<?php
declare (strict_types=1);


namespace Leones\AdamLinkR\Converter;


use EasyRdf\Graph;
use EasyRdf\RdfNamespace;
use EasyRdf\Resource;
use Leones\AdamLinkR\IISGSparql;

/**
 * Class IISGConverter
 * Fetches data from the IISG endpoint and converts into IISGRecords
 *
 * Possible todo: subjects mappen naar geportretteerden (ene Robert)
 * Gebouwnamen ??
 *
 * Also run the StreetnameSeeker class
 *
 * Description:
 * If there is a dc:creator in the IISG data, it is always a URI to IISG authority thing
 */
class IISGConverter
{
    const BATCH_SIZE = 4000;
    const DEV_BATCH  = 50;
    private $limit = 400;
    private $offset = 0;

    private $sparqler;

    public function __construct(IISGSparql $sparql)
    {
        $this->sparqler = $sparql;
    }

    /**
     * Converts IISG metadata record to a graph
     */
    public function convert(string $file, string $format)
    {
        $batch = 0;

        while (true) {
            $graph = $this->initGraph();

            $nonUniqueRecords = $this->sparqler->fetchDataFromIISGEndpointConcerningAmsterdam(
                $this->limit,
                $this->offset
            );

            $this->offset += $this->limit;
            $batch += $this->limit;

            $resultCount = $nonUniqueRecords->numRows();
            $remaining = $resultCount;
            Helper::writeln('OFFSET ' . $this->offset . ', found ' . $resultCount . ' records');

            // stop if no more results
            if ($resultCount < 1) {
                Helper::writeln('No more results, we can quit now.');

                return;
            }

            // add to the batch
            foreach ($nonUniqueRecords as $row) {
                $remaining--;
                $graph = $this->addToGraph($graph, $row);
            }

            // flush the batch to file
            if ($batch > self::BATCH_SIZE || $batch > $remaining) {
                Helper::writeln('flushing to file.');
                file_put_contents($file, $graph->serialise($format), FILE_APPEND | LOCK_EX);
                $graph = null;
                $batch = 0;
            }
        }
    }

    private function getPersonFromIISGAuthority(string $personUri)
    {
        $triples = $this->sparqler->fetchPersonLabelAndViafMatchForPerson($personUri);
        if ($triples->numRows() > 1) {
            // apparently we have multiple names for a person
            Helper::writeln('MULTIPLE FOR ' . $personUri);
            $data = [];
            foreach ($triples as $obj) {
                $row['uri'] = $personUri;
                $row['name'] = (string)$obj->name;
                $row['viaf'] = (string)$obj->viaf;
                $data[] = $row;
            }
            Helper::writeToCsv('iisg-person-names.csv', $data);
        }

        // todo attempt to match with Adamlink via Viaf? if it exists or via name?
        // Vegter, Jaap
    }

    // create a proper uri (from the part about the handle?)
    private function createUri()
    {

    }

    private function createSameAsHandle()
    {
        // 'handle...' ;
        // Wouter en Eric mailen
    }

    private function createIsShownAt()
    {
        // 'https://search.socialhistory.org/Record/1164775' ;
    }

    private function addToGraph(Graph $graph, \stdClass $row): Graph
    {
        /** @var Resource $resource */
        $resource = $graph->resource((string)$row->item, 'rdf:Description');

        // simple dc elements
        $resource->add('dc:identifier', (string)$row->item);
        if (isset($row->dcXtitle)) {
            $resource->add('dc:title', (string)$row->dcXtitle);
        }
        if (isset($row->dcXdescription)) {
            $resource->add('dc:description', (string)$row->dcXdescription);
        }
        if (isset($row->dcXsubject)) {
            $resource->add('dc:subject', (string)$row->dcXsubject);
        }
        if (isset($row->dcXrights)) {
            $resource->add('dc:rights', (string)$row->dcXrights);
        }
        if (isset($row->rdfXtype)) {
            $this->convertStringToUri($resource, (string)$row->rdfXtype);
        }
        if (isset($row->dcXtitle)) {
            $resource->add('dc:title', (string)$row->dcXtitle);
        }

        if (isset($row->dcXcreator)) {
            $creator = (string)$row->dcXcreator;
            if (Helper::isUri($creator)) {
                //Helper::writeln('WE HAVE A URI');
                $this->getPersonFromIISGAuthority($creator);
                $uri = $graph->resource($creator);
                $resource->addResource('dc:creator', $uri);
            } else {
                $resource->add('dc:creator', (string)$row->dcXcreator);
            }
        }

        if (isset($row->dcXdate)) {
            $startDate = \EasyRdf\Literal\Date::create((string)$row->dcXdate, '', 'xsd:date');
            $resource->set('sem:hasBeginTimeStamp', $startDate);

            $endDate = \EasyRdf\Literal\Date::create((string)$row->dcXdate, '', 'xsd:date');
            $resource->set('sem:hasEndTimeStamp', $endDate);
        }

        if (isset($row->dctermsXspatial)) {
            $resource->add('dct:spatial', (string)$row->dctermsXspatial);
        }

        $resource->add('foaf:depiction', $graph->resource((string)$row->imghandlelevel3));

        // add collection
        $resource->addResource('void:inDataset', '<https://data.adamlink.nl/iisg/beeldbank/>');

        // add person
        if (isset($row->dcXsubjectPerson)) {
            $graph->add($resource, 'dc:subject', (string)$row->dcXsubjectPerson);
        } else {
            if (isset($row->dcXsubjectPersonLabel)) {
                // create a blank node of schema/person
                $blank = $graph->newBNode();
                $blank->add('schema:Person', (string)$row->dcXsubjectPersonLabel);
                $resource->add('dc:subject', $blank);
            }
        }

        // add org
        if (isset($row->dcXsubjectOrg)) {
            $graph->add($resource, 'dc:subject', (string)$row->dcXsubjectOrg);
        } else {
            if (isset($row->dcXsubjectOrgLabel)) {
                // create a blank node of schema/person
                $blank = $graph->newBNode();
                $blank->add('schema:Organization', (string)$row->dcXsubjectOrgLabel);
                $resource->add('dc:subject', $blank);
            }
        }

        return $graph;
    }

    /**
     * Creates a csv file with titles, description and subjects to search for names
     */
    public function convertToCsv(string $file)
    {
        $batch = 0;

        while (true) {
            $fakeGraph = [];

            $nonUniqueRecords = $this->sparqler->fetchTextFieldsToSearchForLinkingStreetNames(
                $this->limit,
                $this->offset
            );

            $this->offset += $this->limit;
            $batch += $this->limit;

            $resultCount = $nonUniqueRecords->numRows();
            $remaining = $resultCount;
            print 'OFFSET ' . $this->offset . ', found ' . $resultCount . ' records' . PHP_EOL;

            // stop if no more results
            if ($resultCount < 1) {
                print 'No more results, we are done.' . PHP_EOL;

                return;
            }

            // add to the batch
            foreach ($nonUniqueRecords as $row) {
                $remaining--;
                $fakeGraph[] = $row;
            }

            // flush the batch to file
            if ($batch > self::BATCH_SIZE || $batch > $remaining) {
                print 'flushing to file.' . PHP_EOL;

                Helper::writeToCsv($file, $fakeGraph);

                $fakeGraph = null;
                $batch = 0;
            }
        }
    }

    /**
     *
     * @return Graph
     */
    private function initGraph(): Graph
    {
        RdfNamespace::set('dc', 'http://purl.org/dc/elements/1.1/');
        RdfNamespace::set('dct', 'http://purl.org/dc/terms/');
        RdfNamespace::set('sem', 'http://semanticweb.cs.vu.nl/2009/11/sem/');
        RdfNamespace::set('xsd', 'http://www.w3.org/2001/XMLSchema#');
        RdfNamespace::set('void', 'http://rdfs.org/ns/void#');

        return new Graph();
    }
}
