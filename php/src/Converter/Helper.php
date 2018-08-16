<?php
declare(strict_types=1);


namespace Leones\AdamLinkR\Converter;


class Helper
{
    public static function isUri(string $value): bool
    {
        if (mb_substr(strtolower($value), 0, 4) === 'http' ||
            mb_substr(strtolower($value), 0, 3) === 'urn'
        ) {
            return true;
        }

        return false;
    }

    public static function writeln(string $msg)
    {
        print $msg . PHP_EOL;
    }

    public static function cleanUpString(string $string)
    {
        return str_replace(["\n\r", "\n", "\r", " "], '', $string);
    }

    public static function ensureIsProperUri(string $uri): string
    {
        if (substr($uri, 0, 1) != '<') {
            $uri = '<' . $uri . '>';
        }

        return $uri;
    }

    /**
     * Apparently we have quotes as the first or last character in at least the description.
     * EasyRDF adds tripl quotes which works fine but not if the first or last character is a quote.
     * Then we get 4 """" and Triply does not like that
     *
     * @param string $literalString
     * @return string
     */
    public static function fixQuotesInLiteralString(string $literalString)
    {
        if (mb_substr($literalString, 0, 1, 'utf-8') === '"') {
            $literalString = ' ' . $literalString;
        }
        if (mb_substr($literalString, -1, 1, 'utf-8') === '"') {
            $literalString .= ' ';
        }

        return $literalString;
    }

    public static function stripStuffBetweenBrackets(string $string)
    {
        return preg_replace('/\s*\([^)]*\)/', '', $string);
    }

    public static function csvToArray(
        string $filename,
        string $delimiter = ',',
        string $enclosure = '"',
        string $escape = '\\'
    ) {
        if (! file_exists($filename) || ! is_readable($filename)) {
            throw new \RuntimeException('Sorry the file ' . $filename . ' was not found.');
        }

        $header = null;
        $data = array();
        $lines = file($filename);

        foreach ($lines as $line) {
            $values = str_getcsv($line, $delimiter, $enclosure, $escape);
            if (! $header) {
                $header = $values;
            } else {
                $data[] = array_combine($header, $values);
            }
        }

        return $data;
    }

}
