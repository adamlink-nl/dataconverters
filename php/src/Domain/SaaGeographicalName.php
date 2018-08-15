<?php
declare (strict_types=1);


namespace Leones\AdamLinkR\Domain;


final class SaaGeographicalName
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $numberFrom;

    /**
     * @var int
     */
    private $numberTo;

    private function __construct(
        string $name,
        int $numberFrom,
        int $numberTo
    ) {
        $this->name = $name;
        $this->numberFrom = $numberFrom;
        $this->numberTo = $numberTo;
    }

    public static function fromXml(\SimpleXMLElement $xml)
    {
        return new SaaGeographicalName(
            (string) $xml->name,
            (int) $xml->number_from,
            (int) $xml->number_to
        );
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function numberFrom(): int
    {
        return $this->numberFrom;
    }

    /**
     * @return int
     */
    public function numberTo(): int
    {
        return $this->numberTo;
    }

}
