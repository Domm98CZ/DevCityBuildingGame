<?php declare(strict_types=1);
namespace App\Business\Enums;

enum ResourceType
{
    case WOOD; // stavby + basic nástroje
    case STONE; // lepší stavby + lepší nástroje
    case IRON; // nejlepší stavby + nejlepší nástroje
    case WATER; // voda pro lidi a pole
    case FOOD; // jidlo (pole/lov) pro přežití lidi
    case PEOPLE; // lidi, do armady, na ukoly, poddani
    case PEOPLE_LIMIT; //@TODO: max people limit based on other stats, dunno how to do that rn
}
