<?php

namespace App\Repository;

use App\Entity\BulletinBoardItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * Finds relevant BulletinBoardItems using native SQL full-text search.
     *
     * @param string $userQuery The search query from the user.
     * @param int $limit The maximum number of results to return.
     * @return array<int, array<string, mixed>> Returns an array of results with relevance score.
     */
    public function findRelevantItems(string $userQuery, int $limit = 5): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(BulletinBoardItem::class, 'i');
        $rsm->addFieldResult('i', 'id', 'id');
        $rsm->addFieldResult('i', 'iri', 'iri');
        $rsm->addFieldResult('i', 'title', 'title');
        $rsm->addFieldResult('i', 'department', 'department');
        $rsm->addFieldResult('i', 'agenda', 'agenda');
        $rsm->addFieldResult('i', 'reference_number', 'referenceNumber');
        $rsm->addFieldResult('i', 'published_at', 'publishedAt');
        $rsm->addFieldResult('i', 'relevant_until', 'relevantUntil');
        $rsm->addFieldResult('i', 'detail_url', 'detailUrl');
        $rsm->addFieldResult('i', 'full_text_content', 'fullTextContent');
        $rsm->addFieldResult('i', 'created_at', 'createdAt');
        $rsm->addFieldResult('i', 'updated_at', 'updatedAt');
        $rsm->addScalarResult('relevance', 'relevance');

        $sql = "
            SELECT
                i.*,
                MATCH(i.title, i.full_text_content) AGAINST (:query IN BOOLEAN MODE) as relevance
            FROM
                bulletin_board_item i
            WHERE
                MATCH(i.title, i.full_text_content) AGAINST (:query IN BOOLEAN MODE)
            ORDER BY
                relevance DESC
            LIMIT :limit
        ";

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('query', $userQuery);
        $query->setParameter('limit', $limit, Types::INTEGER);

        return $query->getResult();
    }
}
