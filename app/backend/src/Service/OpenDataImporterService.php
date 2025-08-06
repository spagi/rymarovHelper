<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\BulletinBoardDocument;
use App\Entity\BulletinBoardItem;
use App\Repository\BulletinBoardItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenDataImporterService
{
    private EntityManagerInterface $entityManager;
    private BulletinBoardItemRepository $bulletinBoardItemRepository;
    private PdfTextExtractor $pdfTextExtractor;
    private HttpClientInterface $httpClient;

    private VectorService $vectorService;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        BulletinBoardItemRepository $bulletinBoardItemRepository,
        PdfTextExtractor $pdfTextExtractor,
        HttpClientInterface $httpClient,
        VectorService $vectorService,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->bulletinBoardItemRepository = $bulletinBoardItemRepository;
        $this->pdfTextExtractor = $pdfTextExtractor;
        $this->httpClient = $httpClient;
        $this->vectorService = $vectorService;
        $this->logger = $logger;
    }

    public function run(): void
    {
        $jsonUrl = 'https://portal.rymarov.cz/wab/eud/ODExportData.action';
        $processedCount = 0;
        $createdCount = 0;
        $updatedCount = 0;

        try {
            $this->logger->info('Starting OpenData import from: '.$jsonUrl);
            $response = $this->httpClient->request('GET', $jsonUrl);
            $jsonData = $response->toArray();
        } catch (TransportExceptionInterface|\Exception $e) {
            $this->logger->error(sprintf('Failed to download or parse JSON from %s: %s', $jsonUrl, $e->getMessage()));

            return;
        }

        if (!isset($jsonData['informace']) || !is_array($jsonData['informace'])) {
            $this->logger->warning('JSON data does not contain the expected "informace" array.');

            return;
        }

        foreach ($jsonData['informace'] as $itemData) {
            ++$processedCount;
            $iri = $itemData['iri'] ?? null;

            if (!$iri) {
                $this->logger->warning(sprintf('Skipping item due to missing IRI: %s', json_encode($itemData)));

                continue;
            }

            $bulletinBoardItem = $this->bulletinBoardItemRepository->findOneBy(['iri' => $iri]);

            if (!$bulletinBoardItem) {
                $bulletinBoardItem = new BulletinBoardItem();
                $bulletinBoardItem->setIri($iri);
                $this->entityManager->persist($bulletinBoardItem);
                ++$createdCount;
            } else {
                ++$updatedCount;
            }

            $bulletinBoardItem->setTitle($itemData['název']['cs'] ?? 'Bez názvu');
            $bulletinBoardItem->setDepartment($itemData['odbor'] ?? null);

            $bulletinBoardItem->setAgenda($itemData['agenda'][0]['název']['cs'] ?? null);
            $bulletinBoardItem->setReferenceNumber($itemData['číslo_jednací'] ?? null);
            $bulletinBoardItem->setPublishedAt(new \DateTimeImmutable($itemData['vyvěšení']['datum'] ?? 'now'));

            if (isset($itemData['relevantní_do']['datum'])) {
                $bulletinBoardItem->setRelevantUntil(new \DateTimeImmutable($itemData['relevantní_do']['datum']));
            }
            $bulletinBoardItem->setDetailUrl($itemData['url'] ?? '#');

            $fullTextContent = '';

            if (!$bulletinBoardItem->getDocuments()->isEmpty()) {
                $bulletinBoardItem->getDocuments()->clear();
            }

            if (isset($itemData['dokument']) && is_array($itemData['dokument'])) {
                foreach ($itemData['dokument'] as $documentData) {
                    $documentUrl = $documentData['url'] ?? null;

                    $documentFileName = $documentData['název']['cs'] ?? 'bez_nazvu.pdf';

                    if ($documentUrl) {
                        $document = new BulletinBoardDocument();
                        $document->setFileName($documentFileName);
                        $document->setFileUrl($documentUrl);
                        $bulletinBoardItem->addDocument($document);

                        $extractedText = $this->pdfTextExtractor->extractTextFromUrl($documentUrl);
                        if ($extractedText) {
                            $fullTextContent .= $extractedText."\n\n";
                        }
                    }
                }
            }
            $bulletinBoardItem->setFullTextContent(trim($fullTextContent));
            
            $this->entityManager->flush();

            if ($bulletinBoardItem->getId()) {
                $this->vectorService->addDocument(
                    'bulletin_' . $bulletinBoardItem->getId(),
                    $bulletinBoardItem->getTitle() . "\n\n" . $bulletinBoardItem->getFullTextContent(),
                    [
                        'source' => 'bulletin_board',
                        'url' => $bulletinBoardItem->getDetailUrl(),
                        'title' => $bulletinBoardItem->getTitle(),
                    ]
                );
            }
        }

        $this->entityManager->flush();

        $this->logger->info(sprintf(
            'OpenData import finished. Processed: %d, Created: %d, Updated: %d',
            $processedCount,
            $createdCount,
            $updatedCount
        ));
    }
}