<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findByCampus($campus)
    {
        $qb = $this->createQueryBuilder('s');
        $qb->leftJoin('s.campus', 'c')
            ->leftJoin('s.etat', 'e')
            ->andWhere('s.campus = :campus')
            ->setParameter('campus', $campus)
        ;

        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function findByNonInscrit($participant)
    {
        $qb = $this->createQueryBuilder('s');
        $qb->andWhere('s.participant != :participant')
            ->setParameter('participant', $participant)
        ;

        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function findBySortiePasse()
    {
        $qb = $this->createQueryBuilder('s');
        $qb->leftJoin('s.etat', 'e')
            ->andWhere('s.etat != Passe') ;



        $query = $qb->getQuery();
        return $query->getResult();
    }

    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
