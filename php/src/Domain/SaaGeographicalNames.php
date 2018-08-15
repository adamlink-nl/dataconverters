<?php
declare(strict_types=1);


namespace Leones\AdamLinkR\Domain;


class SaaGeographicalNames implements \IteratorAggregate, \Countable, \JsonSerializable
{
    /** @var SaaGeographicalName[] */
    private $names = [];

    private function __construct(SaaGeographicalName ...$geoNames)
    {
        $this->names = $geoNames;
    }

    public static function fromXML(\SimpleXMLElement $xml): SaaGeographicalNames
    {
        $geoNames = new SaaGeographicalNames();

        $names = $xml->xpath('sk:parameter[@name="geografische naam"]');
        foreach ($names as $element) {
            $geoNames->add(SaaGeographicalName::fromXml($element));
        }

        return $geoNames;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->names);
    }

    public function count(): int
    {
        return count($this->names);
    }

    public function add(SaaGeographicalName $geoName)
    {
        array_push($this->names, $geoName);
    }

    public function JsonSerialize()
    {
        $records = [];
        foreach ($this->names as $record) {
            $records[] = $record->JsonSerialize();
        }
        return $records;
    }
}