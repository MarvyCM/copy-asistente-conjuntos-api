<?php

namespace App\Repository;

use App\Enum\EstadoDescripcionDatosEnum;
use App\Entity\DescripcionDatos;
use App\Entity\OrigenDatos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/*
 * Descripción: Es la clase es la que se utiliza por doctrine cuando se desea hacer 
 *              operaciones de lectura y escritura mas especificas sobre una entidad     
 */

/**
 * @method DescripcionDatos|null find($id, $lockMode = null, $lockVersion = null)
 * @method DescripcionDatos|null findOneBy(array $criteria, array $orderBy = null)
 * @method DescripcionDatos[]    findAll()
 * @method DescripcionDatos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DescripcionDatosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DescripcionDatos::class);
    }


    /**
      * @return DescripcionDatos[] Returns an array of DescripcionDatos objects
     */
    public function getDataUsersByPage($page = 1, $pageSize = 0, $usuario)
    {

       if ($usuario=="ROLE_ADMIN") {
        // build the query for the doctrine paginator
            $query = $this->createQueryBuilder('d')
            ->andWhere('d.estado <> :searchTerm')
            ->setParameter('searchTerm',EstadoDescripcionDatosEnum::BORRADOR)
            ->orderBy('d.id', 'DESC')
            ->getQuery()
            ->getArrayResult();
        } else {
            $query = $this->findBy(array('usuario' => $usuario),(array('id'=>'DESC')));
        }

       if ($pageSize) {
            // load doctrine Paginator
            $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

            // you can get total items
            $totalItems = count($paginator);

            // get total pages
            $pagesCount = ceil($totalItems / $pageSize);

            // now get one page's items:
            $paginator
                ->getQuery()
                ->setFirstResult($pageSize * ($page-1)) // set the offset
                ->setMaxResults($pageSize);
            // return stuff..
            return ["data" => $paginator, "totalElemetos"=>$totalItems, "totalPaginas"=>$pagesCount];
       } else {
           $totalItems = count($query);
           return ["data" => $query, "totalElementos"=>$totalItems, "totalPaginas"=>0];
       }    
   }
        
}
