<?php
declare(strict_types=1);


namespace Leones\AdamLinkR\Sparql;


use EasyRdf\Sparql\Client;
use EasyRdf\Sparql\Result;


final class AdamLinkClient
{
    private $endpoint = 'https://api.data.adamlink.nl/datasets/AdamNet/all/services/endpoint/sparql';

    /** @var Client() */
    private $client;

    public function __construct()
    {
        $this->client = new Client($this->endpoint);
    }

    public function findPersonByName(string $name): Result
    {
        $query = "
        PREFIX owl: <http://www.w3.org/2002/07/owl#>
        PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
        PREFIX schema: <http://schema.org/>
        
        SELECT ?s ?prefLabel
        WHERE {
            ?s skos:prefLabel ?prefLabel .
            #?s a schema:Person .
            OPTIONAL { ?s owl:sameAs ?uri . }
            OPTIONAL { ?s skos:altLabel ?alt . }
            FILTER (?prefLabel = \"{$name}\"^^xsd:string || ?alt = \"{$name}\"^^xsd:string )
         }
        GROUP BY ?s
        ";
        //print $query . PHP_EOL;
        return $this->client->query($query);
    }

    public function findBuildingByName(string $name): Result
    {
        $query = "
        PREFIX owl: <http://www.w3.org/2002/07/owl#>
        PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
        PREFIX schema: <http://schema.org/>
        
        SELECT ?s ?prefLabel
        WHERE {
            #?s schema:label ?name .
            ?s skos:prefLabel ?prefLabel .
            #?s a schema:Person .
            OPTIONAL { ?s owl:sameAs ?uri . }
            OPTIONAL { ?s skos:altLabel ?alt . }
            FILTER (?prefLabel = \"{$name}\"^^xsd:string || ?alt = \"{$name}\"^^xsd:string )
         }
        GROUP BY ?s
        ";
        //print $query . PHP_EOL;
        return $this->client->query($query);
    }
}
