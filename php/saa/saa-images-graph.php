#!/usr/bin/env php
<?php declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';


$sparqler = new \Leones\AdamLinkR\AdamlinkSparql();
$data = $sparqler->test();
var_dump(get_class($data));
//foreach ($data as $row) {
//    print (string) $row->type . ' -- ' . (string) $row->count . PHP_EOL;
//}


$graph = EasyRdf_Graph::newAndLoad('https://data.adamlink.nl/AdamNet/all/services/endpoint#query=PREFIX+rdf%3A+%3Chttp%3A%2F%2Fwww.w3.org%2F1999%2F02%2F22-rdf-syntax-ns%23%3E%0APREFIX+rdfs%3A+%3Chttp%3A%2F%2Fwww.w3.org%2F2000%2F01%2Frdf-schema%23%3E%0APREFIX+foaf%3A+%3Chttp%3A%2F%2Fxmlns.com%2Ffoaf%2F0.1%2F%3E%0ACONSTRUCT+%7B%0A%09%3Fs+foaf%3Adepiction+%3Fo+%0A%7D%0AFROM+%3Chttps%3A%2F%2Fdata.adamlink.nl%2Fsaa%2FImages%2Fgraphs%2Fsaa-images%3E%0AWHERE+%7B+%0A++%3Fs+foaf%3Adepiction+%3Fo%0A++++++FILTER+(%3Fo+!%3D+%3Chttp%3A%2F%2Fimages.memorix.nl%2Fams%2Fthumb%2F640x480%2F.jpg%3E)%0A%7D%0A&contentTypeConstruct=text%2Fturtle&contentTypeSelect=application%2Fsparql-results%2Bjson&endpoint=https%3A%2F%2Fdata.adamlink.nl%2F_api%2Fdatasets%2FAdamNet%2Fall%2Fservices%2Fendpoint%2Fsparql&requestMethod=POST&tabTitle=Query+2&headers=%7B%7D&outputFormat=table');
print $graph->serialise('turtle');
die;

$graph = $sparqler->saaImagesThatAreNotEmpty();
var_dump(get_class($graph));

foreach ($graph as $row) {
    print (string) $row->s .' ' . (string) $row->p . ' ' . (string) $row->o . PHP_EOL;
}
//print "<pre>".$graph->dump('text')."</pre>";

$graph = EasyRdf_Graph::newAndLoad($graph);
print $graph->serialise('turtle');

die;


// load graph from ttl file
$graph = new EasyRdf_Graph();


//$graph->load('http://www.vondel.humanities.uva.nl/ecartico/persons/6292');
$graph->load('https://hart.amsterdam/nl/collectie/object/amcollect/540');
//$graph->parseFile('small.ttl');
print $graph->serialise('turtle');