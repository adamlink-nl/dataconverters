<?php declare(strict_types=1);


namespace Leones\AdamLinkR\Mapper;

use EasyRdf\Sparql\Result;
use Leones\AdamLinkR\SimpleLogger;
use Leones\AdamLinkR\Sparql\AdamLinkClient;


/**
 * Maps persons to an AdamLink URI
 */
final class PersonToAdamLinkPersonMapper
{
    protected $notFoundCache = [];
    protected $foundCache = [];

    /** @var SimpleLogger */
    private $logger;

    public function __construct(SimpleLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return bool|string
     */
    public function map(string $name):string
    {
        // skip if we tried to get this name before and failed
        if (isset($this->notFoundCache[$name])) {
            return '';
        }

        // return found value
        if (isset($this->foundCache[$name])) {
            return $this->foundCache[$name];
        }

        $client = new AdamLinkClient();
        $uri = $this->handleResult($name, $client->findPersonByName($name));

        if (strlen($uri) > 1) {
            $this->foundCache[$name] = $uri;
            return $uri;
        }

        $this->notFoundCache[$name] = 1;
        return '';
    }

    private function handleResult(string $name, Result $sparqlResult): string
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
