<?php declare(strict_types=1);
namespace App\Presentation\Commands;

use App\Services\MapService;
use Nette\Utils\Random;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\Debugger;

#[AsCommand('app:create-island', 'Command for creating new worlds.')]
final class CreateIslandCommand extends Command
{
    public function __construct(
        private readonly MapService $mapService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('code', InputArgument::REQUIRED, 'Code name for new map and island.');
        $this->addArgument('name', InputArgument::REQUIRED, 'Full userfriendlyname for new map and island.');
        $this->addArgument('seed', InputArgument::OPTIONAL, 'Seed/Salt, default causes random string.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $code = $input->getArgument('code');
        $name = $input->getArgument('name');
        $seed = $input->getArgument('seed');

        try {
            if (empty($seed)) {
                $seed = Random::generate(32);
            }

            $output->writeln('<info>>> Game creating new worlds</info>');
            $output->writeln(sprintf('<comment>-- code: %s</comment>', $code));
            $output->writeln(sprintf('<comment>-- name: %s</comment>', $name));
            $output->writeln(sprintf('<comment>-- seed: %s</comment>', $seed));

            $output->writeln('<info>>> Setting up world params</info>');
            $this->mapService->setSeed($seed);

            $output->writeln('<info>>> Generating new world</info>');
            $this->mapService->generateMap($name, $code);

            $output->writeln('<info>>> New world just been created</info>');
            return self::SUCCESS;
        } catch (\Throwable $exception) {
            Debugger::log($exception);
            $output->writeln('<error>Exception during new world creating, check logs</error>');
        }

        return self::FAILURE;
    }
}
