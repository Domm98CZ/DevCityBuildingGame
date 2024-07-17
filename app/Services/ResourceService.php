<?php declare(strict_types=1);
namespace App\Services;

final class ResourceService
{
    // @TODO
    private array $resourceEnum = [
        'wood' // stavby + basic nástroje
        , 'stone' // lepší stavby + lepší nástroje
        , 'iron' // nejlepší stavby + nejlepší nástroje
        , 'water' // voda pro lidi a pole
        , 'food' // jidlo (pole/lov) pro přežití lidi
        , 'people' // lidi, do armady, na ukoly, poddani
        , 'people_cap' // maximalni limit lidi, může být zvětšen stavbou domu
    ];
}
