<?php declare(strict_types=1);
namespace App;

use Nette\Bootstrap\Configurator;
use Tracy\Debugger;

/**
 * Class Bootstrap
 * @package App
 */
final class Bootstrap
{
    /**
     * @return Configurator
     */
    public static function boot(): Configurator
    {
        $configurator = new Configurator();
        $debug_enabled = file_exists(__DIR__ . '/.debug');
        $configurator->setDebugMode($debug_enabled);
        $configurator->enableTracy(__DIR__ . '/../log');
        $configurator->setTimeZone('Europe/Prague');
        $configurator->setTempDirectory(__DIR__ . '/../temp');
        $configurator->addStaticParameters(['env' => $_ENV]);

        $configurator->createRobotLoader()
            ->addDirectory(__DIR__)
            ->register()
        ;

        // Local website configuration
        $configurator
            ->addConfig(__DIR__ . '/../config/config.neon')
        ;

        if (file_exists(__DIR__ . '/../config/local.neon')) {
            $configurator->addConfig(__DIR__ . '/../config/local.neon');
        }

        if($debug_enabled && Debugger::$showBar) {
            Debugger::$maxLength = 99999;
            Debugger::$maxDepth = 15;
            Debugger::getBlueScreen()->keysToHide = [
                'password', 'psswd', 'pwd'
            ];
        }

        return $configurator;
    }
}
