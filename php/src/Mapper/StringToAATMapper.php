<?php
declare(strict_types=1);


namespace Leones\AdamLinkR\Mapper;


use EasyRdf\Resource;
use Leones\AdamLinkR\Converter\Helper;

class StringToAATMapper
{
    protected static $availableTypes = [];

    /** Converts strings supplied by the collections to AAT terms */
    public static function convertDcTypeToUri(Resource $record, string $type): Resource
    {
        if (count(self::$availableTypes) < 1) {
            self::$availableTypes = Helper::csvToArray(
                __DIR__ . '/../../data/aat.csv',
                ';'
            );
        }

        $uri = '';
        // out of all available types attempt to find this one
        foreach (self::$availableTypes as $availableType) {
            // strip funny IISG characters
            $type = trim($type, '\[\]');
            if (strtolower($type) === strtolower($availableType['term'])) {
                $uri = $availableType['aat'];
            }
        }

        if (strlen($uri) > 1) {
            $record->addResource('dc:type', $uri);
        } else {
            $record->set('dc:type', $type);
        }

        return $record;
    }

}
