<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\JsonDownloadException;
use App\Service\OpenDataImporterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-bulletin-board',
    description: 'Imports data from the open data source for bulletin board items.',
)]
class ImportCommand extends Command
{
    private OpenDataImporterService $openDataImporterService;

    public function __construct(OpenDataImporterService $openDataImporterService)
    {
        parent::__construct();
        $this->openDataImporterService = $openDataImporterService;
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you to import bulletin board data from a predefined open data source.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Starting import of bulletin board data...');

        try {
            $this->openDataImporterService->run();
            $io->success('Bulletin board data import completed successfully.');

            return Command::SUCCESS;
        } catch (JsonDownloadException $e) {
            $io->error(sprintf('Import failed: %s', $e->getMessage()));

            return Command::FAILURE;
        } catch (\Exception $e) {
            $io->error(sprintf('An unexpected error occurred during import: %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}
