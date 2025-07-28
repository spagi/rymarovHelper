<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\BulletinBoardItem;

class ContextBuilderService
{
    private const MAX_CONTENT_LENGTH_PER_DOCUMENT = 8000; // Znaků

    public function buildContextFromDbResults(array $dbResults): string
    {
        if (empty($dbResults)) {
            return '';
        }

        $contextParts = [];
        foreach ($dbResults as $resultRow) {
            /** @var BulletinBoardItem $itemObject */
            $itemObject = $resultRow['entity'];

            $title = $this->cleanTextForPrompt($itemObject->getTitle());
            $content = $this->cleanTextForPrompt($itemObject->getFullTextContent());
            $truncatedContent = mb_substr($content, 0, self::MAX_CONTENT_LENGTH_PER_DOCUMENT);
            $contextParts[] = sprintf(
                "Zdroj: %s\nNázev dokumentu: %s\nText dokumentu:\n%s",
                $itemObject->getDetailUrl(), // Přidána URL
                $title,
                $truncatedContent
            );
        }

        return implode("\n\n---\n\n", $contextParts);
    }

    private function cleanTextForPrompt(?string $text): string
    {
        if (null === $text || '' === trim($text)) {
            return '';
        }

        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
