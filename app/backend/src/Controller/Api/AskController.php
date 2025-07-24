<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\BulletinBoardItemRepository;
use App\Service\GeminiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

// OPRAVA: Přidán tento 'use' příkaz
use ApiPlatform\Symfony\Messenger\Processor;
use Symfony\Component\HttpKernel\Attribute\AsController;


// OPRAVA: Označíme třídu jako samostatný kontroler, který API Platform má zaregistrovat
#[AsController]
#[OA\Tag(name: 'AI Assistant')]
class AskController extends AbstractController
{
    // Konstruktor pro injectnutí služeb - je to čistší než je dávat do metody __invoke
    public function __construct(
        private readonly BulletinBoardItemRepository $itemRepository,
        private readonly GeminiService $geminiService
    ) {}

    #[Route('/api/ask', name: 'api_ask', methods: ['POST'])]
    #[OA\Post(
        summary: 'Sends a question to the AI assistant',
        requestBody: new OA\RequestBody(
            description: 'The question to ask the AI',
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'question', type: 'string', example: 'Jaké jsou podmínky pro získání dotace na sport?')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response from the AI',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'answer', type: 'string', example: 'Odpověď od AI asistenta...')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request, for example, a missing question field'
            )
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        // 1. Získání dotazu z těla požadavku
        $data = json_decode($request->getContent(), true);
        $question = $data['question'] ?? null;

        if (empty($question)) {
            return $this->json(['error' => 'Missing "question" field in JSON body.'], 400);
        }

        // 2. Nalezení relevantních dokumentů v naší databázi
        // Použijeme služby injectnuté přes konstruktor
        $relevantItems = $this->itemRepository->findRelevantItems($question, 5);

        if (empty($relevantItems)) {
            return $this->json(['answer' => 'Omlouvám se, ale k vašemu dotazu jsem nenašel žádné relevantní dokumenty na úřední desce.']);
        }

        // 3. Sestavení kontextu pro AI
        $context = '';
        foreach ($relevantItems as $item) {
            $context .= "Název dokumentu: " . $item['title'] . "\n";
            $context .= "Text dokumentu:\n" . $item['full_text_content'] . "\n\n---\n\n";
        }

        // 4. Zeptání se AI a vrácení odpovědi
        $aiResponse = $this->geminiService->getAnswer($question, $context);

        return $this->json(['answer' => $aiResponse]);
    }
}
