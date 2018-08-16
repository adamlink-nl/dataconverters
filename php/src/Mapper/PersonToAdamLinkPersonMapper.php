<?php declare(strict_types=1);


namespace Leones\AdamLinkR\Mapper;

/**
 * Maps persons to an AdamLink URI
 */
final class PersonToAdamLinkPersonMapper extends BaseMapper
{

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

        $uri = $this->handleResult($name, $this->sparqlClient->findPersonByName($name));

        if (strlen($uri) > 1) {
            $this->foundCache[$name] = $uri;
            return $uri;
        }

        $this->notFoundCache[$name] = 1;
        return '';
    }

}
