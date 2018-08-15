<?php declare(strict_types=1);


namespace Leones\AdamLinkR\Mapper;

use Leones\AdamLinkR\SimpleLogger;
use Leones\AdamLinkR\Sparql\AdamLinkClient;


/**
 * Maps persons to an AdamLink URI
 */
final class PersonToAdamLinkPersonMapper
{
    protected $logFile = 'persons_not_found.csv';
    protected $notFoundCache = [];
    protected $foundCache = [];

    public function __construct()
    {
        SimpleLogger::$logFile = $this->logFile;
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
        $uri = $client->findPersonByName($name);

        if (strlen($uri) > 1) {
            $this->foundCache[$name] = $uri;
            return $uri;
        }

        $this->notFoundCache[$name] = 1;
        return '';
    }

}
