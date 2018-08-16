<?php declare(strict_types=1);


namespace Leones\AdamLinkR\Mapper;

use Leones\AdamLinkR\SimpleLogger;
use PDO;

/**
 * Maps a building name to an AdamLink URI
 */
final class BuildingNameToAdamLinkBuildingMapper
{
    protected $logFile = 'buildings_not_found.csv';
    protected $notFoundCache = [];

    public function __construct()
    {
        SimpleLogger::$logFile = $this->logFile;
    }

    public function map(string $name) : string
    {
        // skip if we tried to get this name before
        if (isset($this->notFoundCache[$name])) {
            return '';
        }

        $stmt = $this->pdo->query("set sql_mode=TRADITIONAL");
        $stmt->execute();

        $stmt = $this->pdo->prepare("
            SELECT buildings.id, buildings.name_in_uri, name FROM buildingnames 
            LEFT JOIN buildings ON buildingnames.building_identifier = buildings.id
            WHERE name = :q  
            GROUP BY buildings.id
            ORDER BY preflabel"
        );

        $stmt->execute([
            ':q' => $name,
        ]);
        $found = $stmt->fetch(PDO::FETCH_ASSOC);

        if (count($stmt->rowCount()) > 0 && $found['id'] > 0) {
            return sprintf('https://adamlink.nl/geo/building/%s/%d',
                $found['name_in_uri'], $found['id']
            );
        }

        $this->notFoundCache[$name] = 1;
        $this->logToFile('"' . $name . '"');
        return '';
    }

}
