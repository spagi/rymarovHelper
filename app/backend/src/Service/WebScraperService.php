<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebScraperService
{
    public const BASE_URL = 'https://www.rymarov.cz';

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger
    ) {
    }

    public function searchSite(string $query, int $maxResults = 2): ?string
    {
        $links = $this->getSearchResultLinks($query);
        if (empty($links)) {
            $this->logger->info(sprintf('Web scraper found no links for query: "%s"', $query));

            return null;
        }

        $contentParts = [];
        $count = 0;
        foreach ($links as $link) {
            if ($count >= $maxResults) {
                break;
            }
            $scrapedContent = $this->scrapeArticleContent($link);
            if ($scrapedContent) {
                $contentParts[] = $scrapedContent;
                ++$count;
            }
        }

        return empty($contentParts) ? null : implode("\n\n---\n\n", $contentParts);
    }

    private function getSearchResultLinks(string $query): array
    {
        $searchUrlTemplate = '%s/component/search/?searchphrase=all&Itemid=270&searchword=%s';
        $searchUrl = sprintf($searchUrlTemplate, self::BASE_URL, urlencode($query));

        try {
            $response = $this->client->request('GET', $searchUrl);
            $html = $response->getContent();
            $crawler = new Crawler($html);

            $links = $crawler->filter('dt.result-title a')->each(function (Crawler $node) {
                $href = $node->attr('href');
                if ($href && !str_starts_with($href, 'http')) {
                    return sprintf('%s%s', self::BASE_URL, $href);
                }

                return $href;
            });

            return array_unique(array_filter($links));
        } catch (\Exception $e) {
            $this->logger->error('Failed to get search result links: '.$e->getMessage());

            return [];
        }
    }

    private function scrapeArticleContent(string $url): ?string
    {
        try {
            $response = $this->client->request('GET', $url);
            $html = $response->getContent();
            $crawler = new Crawler($html);

            $contentNode = $crawler->filter('article.news-detail-item .item-page');

            if (0 === $contentNode->count()) {
                $contentNode = $crawler->filter('.page-content');
            }

            if ($contentNode->count() > 0) {
                $text = $contentNode->text(null, true);

                return sprintf("Zdroj: %s\n\n%s", $url, trim($text));
            }

            $this->logger->warning(sprintf('Could not find content node for URL: %s', $url));

            return null;
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Failed to scrape article content from %s: %s', $url, $e->getMessage()));

            return null;
        }
    }
}
