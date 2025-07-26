<?php

namespace App\Repository;

use App\Entity\BulletinBoardItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Types\Types;

/**
 * @extends ServiceEntityRepository<BulletinBoardItem>
 *
 * @method BulletinBoardItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method BulletinBoardItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method BulletinBoardItem[]    findAll()
 * @method BulletinBoardItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BulletinBoardItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BulletinBoardItem::class);
    }

    /**
     * @throws Exception
     */
    public function findRelevantItems(string $userQuery, int $limit = 5): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT
                i.id,
                MATCH(i.title, i.full_text_content) AGAINST (:query IN BOOLEAN MODE) as relevance
            FROM
                bulletin_board_item i
            WHERE
                MATCH(i.title, i.full_text_content) AGAINST (:query IN BOOLEAN MODE) > 0
            ORDER BY
                relevance DESC
            LIMIT :limit
        ';

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('query', $userQuery);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);

        $results = $stmt->executeQuery()->fetchAllAssociative();

        $hydratedResults = [];
        foreach ($results as $result) {
            $item = $this->find($result['id']);
            if ($item) {
                $hydratedResults[] = [
                    'entity' => $item,
                    'relevance' => (float) $result['relevance'],
                ];
            }
        }

        return $hydratedResults;
    }
}
