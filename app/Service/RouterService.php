<?php declare(strict_types=1);

namespace App\Service;

use Nette\Application\Routers\RouteList;

/**
 * Class RouterService
 * @package App\Service
 */
final class RouterService
{
    /**
     * @param string $prefix
     * @return RouteList
     */
    public static function createRouter(string $prefix): RouteList
    {
        $router = new RouteList;

//        $router->addRoute('//' . $prefix . '%domain%/%basePath%/en/<presenter>/<action>[/<id>]', [
//            'locale' => 'en',
//            'module' => 'Web',
//            'presenter' => 'Homepage',
//            'action' => 'default',
//            'id' => null,
//        ]);
        $router->addRoute('//' . $prefix . '%domain%/%basePath%/<presenter>/<action>[/<id>]', [
            'locale' => 'cs',
            'module' => 'Web',
            'presenter' => 'Homepage',
            'action' => 'default',
            'id' => null,
        ]);

        return $router;
    }
}
