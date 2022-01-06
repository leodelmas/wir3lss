<?php

namespace App\Command;

use App\Repository\LogRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

class ClearLogsCommand extends Command
{
    protected static $defaultName = 'app:logs:clear';
    protected static $defaultDescription = 'Delete logs that are a year or older';

    public function __construct(
        private LogRepository $logRepository,
        private EntityManagerInterface $entityManager,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info("Clear logs - Start Treatment");

        $logs = $this->logRepository->findAllOneYearOld();

        if (count($logs) < 1) {
            $io->success('Finished ! No log to delete.');
            return Command::SUCCESS;
        }

        $io->progressStart(count($logs));

        foreach ($logs as $log) {
            $this->entityManager->remove($log);
            $this->entityManager->flush();
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success('Finished !');

        return Command::SUCCESS;
    }
}
