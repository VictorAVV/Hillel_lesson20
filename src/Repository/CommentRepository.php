<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Knp\DoctrineBehaviors\ORM as ORMBehaviors;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    use ORMBehaviors\Tree\Tree;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    // /**
    //  * @return Comment[] Returns an array of Comment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getMaxId()
    {
        return $this->createQueryBuilder('c')
                    ->select('MAX(c.id) as idMax')
                    ->getQuery()
                    ->getSingleResult();
    }

    /**
     * manipulates the flat tree query builder before executing it.
     * Override this method to customize the tree query
     *
     * @param QueryBuilder $qb
     * @param array        $extraParams
     */
    protected function addFlatTreeConditions(\Doctrine\ORM\QueryBuilder $qb, $extraParams)
    {
        $qb = $qb
            ->Join('t.article', 'c_article')
            ->andWhere('c_article.id = :aid')
            ->setParameter('aid', $extraParams['articleId'])
            ;
    }
}
