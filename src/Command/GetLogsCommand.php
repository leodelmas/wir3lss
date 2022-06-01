<?php

namespace App\Command;

use DateTime;
use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetLogsCommand extends Command
{
    protected static $defaultName = 'app:logs:get';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private string $importLogsPath,
        string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        // ...
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info("Import logs - Start Treatment");
        
        foreach (file($this->importLogsPath) as $line) {
            $parts = explode(" ", $line);
            if ($parts[3] == "Request(default/blk_blacklists_bank/-)") {
                $sented = $parts[0] . $parts[1];
                $result = $parts[8] ? $parts[7] . " " . $parts[8] : $parts[7];
                $source = explode('/', $parts[5])[0];
                $log = new Log();
                $log
                    ->setSource($source)
                    ->setDestination($parts[4])
                    ->setSented(DateTime::createFromFormat('Y-m-d H:i:s', $sented))
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
