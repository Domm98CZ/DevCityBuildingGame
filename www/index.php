<?php declare(strict_types=1);

if (file_exists(__DIR__ . '/../www/maintenance.php')) {
    require __DIR__ . '/../www/maintenance.php';
}

use App\Bootstrap;
use Nette\Application\Application;

require __DIR__ . '/../vendor/autoload.php';

$configurator = Bootstrap::boot();
$container = $configurator->createContainer();
$application = $container->getByType(Application::class);
$application->run();
