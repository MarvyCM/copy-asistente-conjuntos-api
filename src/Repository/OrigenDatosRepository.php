<?php

namespace App\Repository;

use App\Entity\OrigenDatos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/*
 * Descripción: Es la clase es la que se utiliza por doctrine cuando se desea hacer 
 *              operaciones de lectura y escritura mas especificas sobre una entidad     
 */

/**
 * @method OrigenDatos|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrigenDatos|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrigenDatos[]    findAll()
 * @method OrigenDatos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrigenDatosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrigenDatos::class);
    }

    // /**
    //  * @return OrigenDatos[] Returns an array of OrigenDatos objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OrigenDatos
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
