<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\BulletinBoardItem;
use App\Entity\WebPage;
use Codewithkyrian\ChromaDB\ChromaDB;
use Codewithkyrian\ChromaDB\Client as ChromaDBClient;
use Codewithkyrian\ChromaDB\Resources\CollectionResource;
use Gemini\Client as GeminiClient;
use Psr\Log\LoggerInterface;

class VectorService
{
    private ChromaDBClient $chromaClient;
    private GeminiClient $geminiClient;
    private LoggerInterface $logger;
    private const COLLECTION_NAME = 'rymarov_documents';

    public function __construct(
        string $geminiApiKey,
        LoggerInterface $logger
    ) {
        $this->chromaClient = ChromaDB::factory()
            ->withHost('chroma')
            ->withPort(8000)
            ->withDatabase('default_database')
            ->withTenant('default_tenant')
            ->withApiVersion('v2')
            ->connect();

        $this->geminiClient = \Gemini::client($geminiApiKey);
        $this->logger = $logger;
    }

    private function createEmbedding(string $text): array
    {
        $response = $this->geminiClient->embeddingModel('text-embedding-004')
            ->embedContent($text);

        return $response->embedding->values;
    }

    private function getOrCreateCollection(): CollectionResource
    {
        return $this->chromaClient->getOrCreateCollection(self::COLLECTION_NAME);
    }

    public function addDocument(string $id, string $text, array $metadata): void
    {
        try {
            $collection = $this->getOrCreateCollection();
            $embedding = $this->createEmbedding($text);

            $collection->add(
                ids: [$id],
                embeddings: [$embedding],
                metadatas: [$metadata],
                documents: [$text]
            );
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Failed to add document to ChromaDB (ID: %s): %s', $id, $e->getMessage()));
        }
    }

    public function findRelevantDocuments(string $query, int $limit = 3): array
    {
        try {
            $collection = $this->getOrCreateCollection();
            $queryEmbedding = $this->createEmbedding($query);

            $results = $collection->query(
                queryEmbeddings: [$queryEmbedding],
                nResults: $limit
            );

            $documents = [];
            if (!empty($results->ids[0])) {
                foreach ($results->ids[0] as $index => $id) {
                    $documents[] = [
                        'id' => $id,
                        'metadata' => (array) $results->metadatas[0][$index],
                        'distance' => $results->distances[0][$index],
                        'content' => $results->documents[0][$index] ?? '',
                    ];
                }
            }
            return $documents;

        } catch (\Exception $e) {
            $this->logger->error(sprintf('Failed to query ChromaDB for query "%s": %s', $query, $e->getMessage()));
            return [];
        }
    }
}
