<?php

declare(strict_types=1);

namespace App\Service;

use Gemini\Client;
use Gemini;
use Gemini\Enums\ModelVariation;
use Psr\Log\LoggerInterface;

class GeminiService
{
    private Client $client;
    private LoggerInterface $logger;

    // Díky autowiringu a konfiguraci v services.yaml se sem automaticky
    // dosadí hodnota ze Symfony Secrets.
    public function __construct(string $geminiApiKey, LoggerInterface $logger)
    {
        // Vytvoříme klienta při inicializaci služby
        $this->client = Gemini::client($geminiApiKey);
        $this->logger = $logger;
    }

    /**
     * Vezme dotaz a kontext, pošle ho AI a vrátí odpověď.
     *
     * @param string $question Dotaz od uživatele
     * @param string $context Textový kontext z naší databáze
     * @return string Odpověď od AI
     */
    public function getAnswer(string $question, string $context): string
    {
        // Sestavení promptu pro AI. Je důležité dát AI jasné instrukce.
        $prompt = <<<PROMPT
Jsi přátelský a nápomocný asistent města Rýmařov. Tvojí úlohou je odpovídat na dotazy občanů.
Odpovídej stručně, jasně, v češtině a formátuj odpověď pomocí Markdownu, pokud je to vhodné (např. odrážky).
Vycházej POUZE z informací poskytnutých v níže uvedeném KONTEXTU.
Pokud v kontextu odpověď není, v žádném případě si nic nevymýšlej a odpověz: "Omlouvám se, ale k tomuto tématu nemám v databázi žádné konkrétní informace."

---
KONTEXT:
{$context}
---

OTÁZKA UŽIVATELE:
{$question}
PROMPT;

        try {
            // Použijeme rychlý a efektivní model Flash
            $response = $this->client->generativeModel(ModelVariation::FLASH)->generateContent($prompt);

            return $response->text();

        } catch (\Exception $e) {
            $this->logger->error('Gemini API call failed: ' . $e->getMessage());

            return 'Omlouvám se, ale došlo k technické chybě při komunikaci s AI. Zkuste to prosím později.';
        }
    }
}
