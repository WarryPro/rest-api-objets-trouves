<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

     /**
      * @return Item[] Returns an array of Item objects
      */

    public function search($term)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.title LIKE :searchTerm OR i.city LIKE :searchTerm')
            ->setParameter('searchTerm', '%'.$term.'%')
            ->orderBy('i.id', 'DESC')
//            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function homeSearch($date, $city, $title) {
        return $this->createQueryBuilder('i')
                ->andWhere('i.title LIKE :title AND i.createdAt BETWEEN :date AND :now AND i.city LIKE :city')
                ->setParameter(':date', $date)
                ->setParameter(':now', new \DateTime('now'))
                ->setParameter(':city', '%' . $city . '%')
                ->setParameter(':title', '%' . $title . '%')
                ->orderBy('i.id', 'DESC')
                ->getQuery()
                ->getResult();
    }


    /*
    public function findOneBySomeField($value): ?Item
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
