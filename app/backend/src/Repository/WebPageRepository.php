<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\WebPage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WebPage>
 *
 * @method WebPage|null find($id, $lockMode = null, $lockVersion = null)
 * @method WebPage|null findOneBy(array $criteria, array $orderBy = null)
 * @method WebPage[]    findAll()
 * @method WebPage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WebPageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebPage::class);
    }

    /**
     * @return array<int, array<string, mixed>>
     *
     * @throws Exception
     */
    public function findRelevantPages(string $query, int $limit = 3): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT
            w.title,
            w.url,
            w.content,
            MATCH(w.title, w.content) AGAINST (:query IN BOOLEAN MODE) as relevance
        FROM
            web_page w
        WHERE
            MATCH(w.title, w.content) AGAINST (:query IN BOOLEAN MODE) > 0
        ORDER BY
            relevance DESC
        LIMIT :limit
    ';
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('query', $query);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }
}
