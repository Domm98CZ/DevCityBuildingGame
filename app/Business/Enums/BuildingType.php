<?php declare(strict_types=1);
namespace App\Business\Enums;

enum BuildingType
{
    case HOUSE; // people
    case FARM; // jídlo
    case WELL; // voda
    case FORTS; // hradby
    case BARRACKS; // kasárny
    case WAREHOUSE; // skladiště s limitem na věci
    case WATCHTOWER; // strážná věž
}
