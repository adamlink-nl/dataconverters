<?php declare(strict_types=1);


namespace Leones\AdamLinkR\Mapper;

/**
 * Maps a street name to an AdamLink URI
 */
final class StreetNameToAdamLinkUriMapper extends BaseMapper
{
    const ADAMLINK_API = 'https://adamlink.nl/api/search/?q=';

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

        $uri = $this->handleResult($name, $this->sparqlClient->findStreetByName($name));

        if (strlen($uri) > 1) {
            $this->foundCache[$name] = $uri;
            return $uri;
        }

        $this->notFoundCache[$name] = 1;
        return '';
    }

    // USING the adamlik API
    public static function mapWithApi(string $name) : string
    {
        $response = file_get_contents(self::ADAMLINK_API . urlencode($name));
        if (($streets = json_decode($response, true)) != null) {
            if ($streets['hits'] === 1) {
                return $streets['results'][0]['uri'];
            } else {
                $msg = $name . " ({$streets['hits']})";
                file_put_contents(self::$logFile, $msg . PHP_EOL , FILE_APPEND);
            }
        }
    }

}
