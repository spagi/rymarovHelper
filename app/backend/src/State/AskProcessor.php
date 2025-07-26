<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\AskInput;
use App\Dto\AskOutput;
use App\Repository\BulletinBoardItemRepository;
use App\Service\ContextBuilderService;
use App\Service\GeminiService;

final class AskProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly BulletinBoardItemRepository $itemRepository,
        private readonly GeminiService $geminiService,
        private readonly ContextBuilderService $contextBuilder
    ) {}

    /**
     * @param AskInput $data The input DTO with the user's question
     * @return AskOutput The output DTO with the AI's answer
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AskOutput
    {
        $question = $data->question;

        $relevantResults = $this->itemRepository->findRelevantItems($question, 5);

        $output = new AskOutput();

        if (empty($relevantResults)) {
            $output->answer = 'Omlouvám se, ale k vašemu dotazu jsem nenašel žádné relevantní dokumenty na úřední desce.';
            return $output;
        }

        $contextString = $this->contextBuilder->buildContextFromDbResults($relevantResults);
        $aiResponse = $this->geminiService->getAnswer($question, $contextString);

        $output->answer = $aiResponse;

        return $output;
    }
}
