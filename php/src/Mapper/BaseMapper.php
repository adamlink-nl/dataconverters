<?php declare(strict_types=1);


namespace Leones\AdamLinkR\Mapper;

use EasyRdf\Sparql\Result;
use Leones\AdamLinkR\SimpleLogger;
use Leones\AdamLinkR\Sparql\SparqlClient;


/**
 * Shared mapper functionality
 */
class BaseMapper
{
    protected $notFoundCache = [];
    protected $foundCache = [];

    /** @var SimpleLogger */
    protected $logger;

    /** @var SparqlClient */
    protected $sparqlClient;

    public function __construct(SparqlClient $sparqlClient, SimpleLogger $logger)
    {
        $this->logger = $logger;
        $this->sparqlClient = $sparqlClient;
    }

    protected function handleResult(string $name, Result $sparqlResult): string
    {
        if ($sparqlResult->numRows() < 1) {
            $this->logger->logToFile('No adamlink URI for ' . $name);
        }
        if ($sparqlResult->numRows() > 1) {
            $this->logger->logToFile('Multiple adamlink URIs for ' . $name);
        }
        if ($sparqlResult->numRows() === 1) {
            $uri = (string) current($sparqlResult)->s;
            $this->logger->logToFile('Found 1 adamlink URI for ' . $name . ' uri: ' . $uri);
            return $uri;
        }
        return '';
    }
}
