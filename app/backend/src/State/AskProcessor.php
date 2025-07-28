<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\AskInput;
use App\Dto\AskOutput;
use App\Repository\BulletinBoardItemRepository;
use App\Repository\WebPageRepository;
use App\Service\ContextBuilderService;
use App\Service\GeminiService;
use App\Service\ScrapingDecisionService;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;

final class AskProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly BulletinBoardItemRepository $itemRepository,
        private readonly GeminiService $geminiService,
        private readonly ContextBuilderService $contextBuilder,
        private readonly WebPageRepository $webPageRepository,
        private readonly ScrapingDecisionService $decisionService,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param AskInput $data The input DTO with the user's question
     *
     * @return AskOutput The output DTO with the AI's answer
     *
     * @throws Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AskOutput
    {
        $question = $data->question;
        $output = new AskOutput();

        $dbResultsWithScore = $this->itemRepository->findRelevantItems($question, 3);

        $contextString = $this->contextBuilder->buildContextFromDbResults($dbResultsWithScore);

        if ($this->decisionService->shouldScrapeWeb($dbResultsWithScore)) {
            $this->logger->info('Decision service says: Scrape the web...');

            $webResults = $this->webPageRepository->findRelevantPages($question, 2);
            $webContext = '';
            foreach ($webResults as $result) {
                $webContext .= sprintf(
                    "Zdroj: %s\n\n%s\n\n",
                    $result['url'],
                    $result['content']
                );
            }

            if (!empty(trim($webContext))) {
                $contextString .= sprintf(
                    "\n\n---\nDALŠÍ INFORMACE Z WEBU MĚSTA (www.rymarov.cz):\n%s",
                    trim($webContext)
                );
            }
        }

        if (empty(trim($contextString))) {
            $output->answer = 'Omlouvám se, ale k vašemu dotazu jsem nenašel žádné relevantní informace.';

            return $output;
        }

        $aiResponse = $this->geminiService->getAnswer($question, trim($contextString));
        $output->answer = $aiResponse;

        return $output;
    }
}
