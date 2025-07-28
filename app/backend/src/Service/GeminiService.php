<?php

declare(strict_types=1);

namespace App\Service;

use Gemini\Client;
use Psr\Log\LoggerInterface;

class GeminiService
{
    private Client $client;
    private LoggerInterface $logger;

    // Díky autowiringu a konfiguraci v services.yaml se sem automaticky
    // dosadí hodnota ze Symfony Secrets.
    public function __construct(string $geminiApiKey, LoggerInterface $logger)
    {
        $this->client = \Gemini::client($geminiApiKey);

        $this->logger = $logger;
    }

    /**
     * Vezme dotaz a kontext, pošle ho AI a vrátí odpověď.
     *
     * @param string $question Dotaz od uživatele
     * @param string $context  Textový kontext z naší databáze
     *
     * @return string Odpověď od AI
     */
    public function getAnswer(string $question, string $context): string
    {
        $prompt = <<<PROMPT
Jsi "Rýmařovský asistent", přátelský a expertní chatbot města Rýmařov. Tvá osobnost je nápomocná, přesná a důvěryhodná.

Tvůj úkol: Na základě informací poskytnutých v níže uvedeném KONTEXTU, odpověz na OTÁZKU UŽIVATELE.

Pravidla pro odpověď:
1.  **Jazyk a styl:** Odpovídej vždy v češtině. Buď stručný, ale informativní. Používej přátelský, ale profesionální tón.
2.  **Formátování:** Pro lepší čitelnost používej Markdown. Klíčové informace (např. částky, termíny) zvýrazni **tučně**. Používej odrážky (`* `) pro seznamy.
3.  **Syntéza informací:** Pokud KONTEXT obsahuje více zdrojů, pokus se informace zkombinovat do jedné plynulé a logické odpovědi. Neopisuj jen jeden zdroj.
4.  **Drž se faktů:** Vycházej POUZE z informací v KONTEXTU. Pokud si nejsi jistý, raději informaci neuváděj. V žádném případě si nic nevymýšlej.
5.  **Citace zdrojů:** Na úplný konec odpovědi, pod samostatný nadpis `### Více informací`, přidej seznam všech URL adres, které jsou v KONTEXTU označeny jako "Zdroj:". Každý odkaz uveď na samostatném řádku jako odrážku.
6.  **Pokud odpověď neznáš:** Pokud v KONTEXTU nenajdeš absolutně žádnou relevantní informaci k otázce, odpověz pouze: "Omlouvám se, ale k tomuto tématu se mi nepodařilo najít žádné konkrétní informace."

---
KONTEXT:
{$context}
---

OTÁZKA UŽIVATELE:
{$question}
PROMPT;
        $this->logger->info('Sending prompt to Gemini: '.$prompt);

        try {
            // Použijeme rychlý a efektivní model Flash
            $response = $this->client->generativeModel(model: 'gemini-2.0-flash')->generateContent($prompt);

            return $response->text();
        } catch (\Exception $e) {
            return 'DEBUGGING ERROR: '.get_class($e).' - '.$e->getMessage();
        }
    }
}
