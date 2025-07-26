<?php

declare(strict_types=1);

namespace App\Service;

class ScrapingDecisionService
{
    private const RELEVANCE_THRESHOLD = 5.0;

    /**
     * Rozhodne, zda se má na základě výsledků z databáze spustit web scraper.
     *
     * @param array<int, array{'entity': \App\Entity\BulletinBoardItem, 'relevance': float}> $dbResultsWithScore
     * @return bool True, pokud se má scrapovat, jinak false.
     */
    public function shouldScrapeWeb(array $dbResultsWithScore): bool
    {

        if (empty($dbResultsWithScore)) {
            return true;
        }
        
        $topResultRow = $dbResultsWithScore[0];

        if ($topResultRow['relevance'] < self::RELEVANCE_THRESHOLD) {
            return true;
        }

        return false;
    }
}
