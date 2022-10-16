<?php

namespace App\Command;

use DateTime;
use App\Entity\Log;
use App\Repository\LogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetLogsCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'app:logs:get';
    protected static $defaultDescription = 'Get logs from file';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private string $importLogsPath,
        private LogRepository $logRepository,
        string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info("Import logs - Start Treatment");

        if (!$this->lock()) {
            $io->warning('The command is already running in another process.');
            return Command::SUCCESS;
        }
        
        foreach (file($this->importLogsPath) as $line) {
            $parts = explode(" ", $line);
            $lastImportedLog = $this->logRepository->findLastImported();
            $sented = DateTime::createFromFormat('Y-m-d H:i:s', $parts[0] . $parts[1]);
            if (str_contains($parts[3], "Request(default/blk_blacklists_") && $sented > $lastImportedLog->getSented()) {
                $result = $parts[8] ? $parts[7] . " " . $parts[8] : $parts[7];
                $source = explode('/', $parts[5])[0];
                $log = new Log();
                $log
                    ->setSource($source)
                    ->setDestination($parts[4])
                    ->setSented($sented)
                    ->setUser($parts[6])
                    ->setResult($result);
                $this->entityManager->persist($log);
            }
        }
        $this->entityManager->flush();
        $io->success('Finished ! Successfully imported logs.');
        return Command::SUCCESS;
    }
}
