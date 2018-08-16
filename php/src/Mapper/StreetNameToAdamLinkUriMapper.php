<?php declare(strict_types=1);


namespace Leones\AdamLinkR\Mapper;

use PDO;

/**
 * Maps a street name to an AdamLink URI
 */
final class StreetNameToAdamLinkUriMapper
{
    const ADAMLINK_API = 'https://adamlink.nl/api/search/?q=';

    protected $logFile = 'street_not_found.csv';

    public function __construct()
    {

    }

    // USING the local database
    public function map(string $name) : string
    {
        // skip if we tried to get this name before
        if (isset($this->notFoundCache[$name])) {
            return '';
        }

        // get exact matches
        $stmt = $this->pdo->prepare("SELECT streets.id, streets.name_in_uri, name FROM streetnames
                                        LEFT JOIN streets ON streetnames.street_identifier = streets.id
                                        WHERE name = :q 
                                        GROUP BY streets.id
                                        ORDER BY preflabel");
        $stmt->execute([
            ':q' => $name,
        ]);
        $found = $stmt->fetch(PDO::FETCH_ASSOC);

        if (count($stmt->rowCount()) > 0 && $found['id'] > 0) {
            return sprintf('https://adamlink.nl/geo/street/%s/%d',
                $found['name_in_uri'], $found['id']
            );
        }

        $this->notFoundCache[$name] = 1;
        $this->logToFile('"' . $name . '"');
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
