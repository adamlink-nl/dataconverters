<?php


namespace Leones\AdamLinkR\Sparql;


use EasyRdf\Sparql\Result;

interface SparqlClient
{

    public function findPersonByName(string $name): Result;

    public function findBuildingByName(string $name): Result;

    public function findStreetByName(string $name): Result;

}