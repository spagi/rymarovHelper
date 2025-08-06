<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\CrawlConfig;
use App\Factory\WebPageDataFactory;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CrawlerService
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly WebPageService $webPageService,
        private readonly WebPageDataFactory $webPageDataFactory,
        private readonly VectorService $vectorService,
        private readonly LoggerInterface $logger
    ) {
    }

    public function crawl(CrawlConfig $config): void
    {
        $queue = new \SplQueue();
        $queue->enqueue($config->startUrl);
        $visited = [];
        $processedCount = 0;

        $this->logger->info(sprintf('Starting crawl for domain "%s"', $config->domain));

        while (!$queue->isEmpty() && $processedCount < $config->limit) {
            $url = $queue->dequeue();

            if ($this->shouldSkipUrl($url, $visited, $config->domain)) {
                continue;
            }

            $visited[$url] = true;
            ++$processedCount;

            $this->logger->info(sprintf('Crawling [%d/%d]: %s', $processedCount, $config->limit, $url));

            try {
                $response = $this->client->request('GET', $url);

                $contentType = $response->getHeaders(false)['content-type'][0] ?? 'text/html';
                if (!str_contains($contentType, 'text/html')) {
                    $this->logger->info(sprintf('Skipping non-html content type "%s" at %s', $contentType, $url));

                    continue;
                }

                $html = $response->getContent();
                $crawler = new Crawler($html, $url);

                $contentData = $this->extractContent($crawler);

                $webPageData = $this->webPageDataFactory->create();
                $webPageData->title = $contentData['title'];
                $webPageData->content = $contentData['content'];
                $webPageData->url = $url;

                $this->webPageService->createOrUpdate($webPageData);

                // Přidání do Vector DB
                $this->vectorService->addDocument(
                    'web_' . md5($url),
                    $contentData['title'] . "\n\n" . $contentData['content'],
                    [
                        'source' => 'website',
                        'url' => $url,
                        'title' => $contentData['title'],
                    ]
                );

                foreach ($this->extractLinks($crawler, $url, $config->domain) as $newLink) {
                    if (!isset($visited[$newLink])) {
                        $queue->enqueue($newLink);
                    }
                }

                if (0 === $processedCount % 20) {
                    $this->webPageService->flush();
                }
            } catch (\Exception $e) {
                $this->logger->error(sprintf('Failed to crawl URL %s: %s', $url, $e->getMessage()));
            }
        }

        $this->webPageService->flush();
        $this->logger->info('Crawling finished.');
    }

    /**
     * Extrahuje název a hlavní textový obsah ze stránky.
     */
    private function extractContent(Crawler $crawler): array
    {
        $title = $crawler->filter('title')->text('Bez názvu');

        $contentNode = $crawler->filter('#sp-component');

        if (0 === $contentNode->count()) {
            $contentNode = $crawler->filter('#content');
        }

        if (0 === $contentNode->count()) {
            $contentNode = $crawler->filter('main');
        }

        $content = '';
        if ($contentNode->count() > 0) {
            $htmlContent = $contentNode->html();

            $textOnly = strip_tags($htmlContent);

            $content = trim(preg_replace('/\s+/', ' ', $textOnly));
        }

        return ['title' => trim($title), 'content' => $content];
    }

    /**
     * Extrahuje a normalizuje všechny platné odkazy ze stránky.
     */
    private function extractLinks(Crawler $crawler, string $currentUrl, string $domain): \Generator
    {
        $baseUri = new Uri($currentUrl);

        foreach ($crawler->filter('a') as $nodeElement) {
            $node = new Crawler($nodeElement);
            $href = $node->attr('href');

            if (empty($href)) {
                continue;
            }

            try {
                $absoluteUri = UriResolver::resolve($baseUri, new Uri($href));
                $uriString = (string) $absoluteUri;

                if ($this->shouldSkipUrl($uriString, [], $domain)) {
                    continue;
                }

                yield strtok($uriString, '#?'); // Vracíme URL bez kotvy a parametrů
            } catch (\InvalidArgumentException $e) {
                // Ignorujeme neplatné odkazy, např. 'javascript:void(0);'
            }
        }
    }

    /**
     * Rozhodovací metoda, zda má být URL přeskočena.
     */
    private function shouldSkipUrl(string $url, array $visited, string $domain): bool
    {
        if (isset($visited[$url])) {
            return true;
        }

        $urlHost = parse_url($url, PHP_URL_HOST);
        if (null === $urlHost || str_replace('www.', '', $urlHost) !== $domain) {
            return true;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if ($path && preg_match('/\.(pdf|jpg|jpeg|png|gif|zip|doc|docx|xls|xlsx)$/i', $path)) {
            return true;
        }

        if (preg_match('/^(mailto|tel):/i', $url)) {
            return true;
        }

        return false;
    }
}
