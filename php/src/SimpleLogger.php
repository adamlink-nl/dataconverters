<?php
declare(strict_types=1);

namespace Leones\AdamLinkR;

class SimpleLogger
{
    public static $logFile = '/../adamlink.log';

    public static function logToCsv(string $msg)
    {
        $fp = fopen(self::$logFile, 'a');
        fwrite($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
        fputcsv($fp, [$msg]);
        fclose($fp);
    }

    public static function logToFile(string $msg)
    {
        file_put_contents(self::$logFile, $msg . PHP_EOL, FILE_APPEND);
    }
}
