<?php

namespace App\Repository;

use App\Entity\BulletinBoardDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BulletinBoardDocument>
 */
class BulletinBoardDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BulletinBoardDocument::class);
    }
}
