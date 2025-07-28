<?php

declare(strict_types=1);

namespace App\Command;

use App\Factory\CrawlConfigFactory;
use App\Service\CrawlerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:crawl-website',
    description: 'Crawls the rymarov.cz website and stores the content.',
)]
class CrawlWebsiteCommand extends Command
{
    public function __construct(
        private readonly CrawlerService $crawlerService,
        private readonly CrawlConfigFactory $configFactory
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $config = $this->configFactory->create('https://www.rymarov.cz', 2000);

            $io->info(sprintf('Starting crawl for domain "%s" with a limit of %d pages.', $config->domain, $config->limit));

            $this->crawlerService->crawl($config);

            $io->success('Website crawling completed successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('An error occurred during crawling: %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}
