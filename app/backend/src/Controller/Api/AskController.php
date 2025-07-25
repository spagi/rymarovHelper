<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Dto\AskInput;
use App\Dto\AskOutput;
use App\Repository\BulletinBoardItemRepository;
use App\Service\GeminiService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
final class AskController
{
    public function __construct(
        private readonly BulletinBoardItemRepository $itemRepository,
        private readonly GeminiService               $geminiService,
        private readonly SerializerInterface         $serializer
    )
    {
    }

    public function __invoke(Request $request): AskOutput
    {
        /** @var AskInput $input */
        $input = $this->serializer->deserialize($request->getContent(), AskInput::class, 'json');

        $question = $input->question;

        if (null === $question) {
            $output = new AskOutput();
            $output->answer = 'Chyba: Dotaz (question) v těle požadavku chybí nebo je prázdný.';
            return $output;
        }

        $relevantItems = $this->itemRepository->findRelevantItems($question, 5);

        if (empty($relevantItems)) {
            $output = new AskOutput();
            $output->answer = 'Omlouvám se, ale k vašemu dotazu jsem nenašel žádné relevantní dokumenty na úřední desce.';
            return $output;
        }

        $context = '';
        foreach ($relevantItems as $resultRow) {
            /** @var BulletinBoardItem $itemObject */
           $itemObject = $resultRow[0];

            $context .= "Název dokumentu: " . $itemObject->getTitle() . "\n";
            $context .= "Text dokumentu:\n" . $itemObject->getFullTextContent() . "\n\n---\n\n";
        }


        $aiResponse = $this->geminiService->getAnswer($question, $context);

        $output = new AskOutput();
        $output->answer = $aiResponse;

        return $output;
    }
}
