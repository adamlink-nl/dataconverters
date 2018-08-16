<?php
declare(strict_types=1);

// bootstrap for converters
use Leones\AdamLinkR\Mapper\BuildingNameToAdamLinkBuildingMapper;
use Leones\AdamLinkR\Mapper\PersonToAdamLinkPersonMapper;
use Leones\AdamLinkR\Mapper\StreetNameToAdamLinkUriMapper;
use Leones\AdamLinkR\SimpleLogger;
use Leones\AdamLinkR\Sparql\AdamLinkClient;

$logDir = __DIR__ . '/logs';

$personMapper = new PersonToAdamLinkPersonMapper(
    new AdamLinkClient(),
    new SimpleLogger($logDir . '/person.log')
);

$buildingMapper = new BuildingNameToAdamLinkBuildingMapper(
    new AdamLinkClient(),
    new SimpleLogger($logDir . '/building.log')
);

$streetMapper = new StreetNameToAdamLinkUriMapper(
    new AdamLinkClient(),
    new SimpleLogger($logDir . '/street.log')
);

