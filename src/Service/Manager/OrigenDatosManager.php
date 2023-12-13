<?php

namespace App\Service\Manager;

use App\Entity\OrigenDatos;
use App\Repository\OrigenDatosRepository;
use Doctrine\ORM\EntityManagerInterface;

/*
 * Descripción: Es el repositorio del origen de los datos
 *              realiza las operaciones de persistencia sobre el ORM
*/

class OrigenDatosManager
{

    private $em;
    private $origenDatosRepository;

    public function __construct(EntityManagerInterface $em, OrigenDatosRepository $origenDatosRepository)
    {
        $this->em = $em;
        $this->origenDatosRepository = $origenDatosRepository;
    }

    public function find(int $id): ?OrigenDatos
    {
        return $this->origenDatosRepository->find($id);
    }


    public function getRepository(): OrigenDatosRepository
    {
        return $this->origenDatosRepository;
    }

    public function create(): OrigenDatos
    {
        $origenDatos = new OrigenDatos();
        return $origenDatos;
    }

    public function persist(OrigenDatos $origenDatos): OrigenDatos
    {
        $this->em->persist($origenDatos);
        return $origenDatos;
    }

    public function save(OrigenDatos $origenDatos): OrigenDatos
    {
        $this->em->persist($origenDatos);
        $this->em->flush();
        return $origenDatos;
    }

    public function reload(OrigenDatos $origenDatos): OrigenDatos
    {
        $this->em->refresh($origenDatos);
        return $origenDatos;
    }

    public function delete(OrigenDatos $origenDatos)
    {
        $this->em->remove($origenDatos);
        $this->em->flush();
    }
}