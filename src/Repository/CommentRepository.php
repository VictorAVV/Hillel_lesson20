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
        if (!empty($extraParams)) {
            $qb = $qb
                ->Join('t.article', 'c_article')
                ->andWhere('c_article.id = :aid')
                ->setParameter('aid', $extraParams['articleId'])
                ;
        }
    }

    /**
     * get all root comments of article with number of child nodes
     * @param $articleId - id of article
     */
    public function getAllRootCommentsOfArticle($articleId)
    {  
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            "SELECT 
                c1, 
	            (SELECT 
                    COUNT(c2.id) 
                From 
                    App\Entity\Comment as c2 
                Where 
                    c2.materializedPath = CONCAT('/', c1.id) 
     	            OR c2.materializedPath LIKE CONCAT('/', c1.id, '/%') 
                ) as numberOfChild
            FROM 
                App\Entity\Comment c1
            WHERE 
                c1.article = :articleId
                AND c1.materializedPath = ''"
        )->setParameter('articleId', $articleId);
    
        return $query->execute();
    }  

    /**
     * @param $articleId - id of article
     * @return count all comments of article
     */
    public function getCountAllCommentsOfArticle($articleId)
    { 
      return  $this->createQueryBuilder('c')
        ->select('count(c.id)')
        ->andWhere('c.article = :articleId')
        ->setParameter('articleId', $articleId)
        ->getQuery()
        ->getSingleScalarResult();
    }
}
