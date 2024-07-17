<?php declare(strict_types=1);
namespace App\Services;

use Nette\Application\Routers\RouteList;

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

        $router->withDomain('api.%domain%')
            ->addRoute('<presenter>/<action>[/<id>]', [
                'module' => 'Api',
                'presenter' => 'Homepage',
                'action' => 'default',
                'id' => null,
            ])
        ;
        $router->withDomain('play.%domain%')
            ->addRoute('<presenter>/<action>[/<id>]', [
                'module' => 'Game',
                'presenter' => 'Homepage',
                'action' => 'default',
                'id' => null,
            ])
        ;

        $router->withDomain('%domain%')
            ->addRoute('<presenter>/<action>[/<id>]', [
                'module' => 'Web',
                'presenter' => 'Homepage',
                'action' => 'default',
                'id' => null,
            ])
        ;

        return $router;
    }
}
