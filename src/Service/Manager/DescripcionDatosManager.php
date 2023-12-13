<?php

namespace App\Service\Manager;

use App\Entity\DescripcionDatos;
use App\Repository\DescripcionDatosRepository;
use Doctrine\ORM\EntityManagerInterface;

/*
 * Descripción: Es el repositorio de la descripcion de los datos
 *              realiza las operaciones de persistencia sobre el ORM
*/

class DescripcionDatosManager
{

    private $em;
    private $descripcionDatosRepository;

    public function __construct(EntityManagerInterface $em, DescripcionDatosRepository $descripcionDatosRepository)
    {
        $this->em = $em;
        $this->descripcionDatosRepository = $descripcionDatosRepository;
    }

    public function find(int $id): ?DescripcionDatos
    {
        return $this->descripcionDatosRepository->find($id);
    }

    public function getRepository(): DescripcionDatosRepository
    {
        return $this->descripcionDatosRepository;
    }

    public function create(): DescripcionDatos
    {
        $descripcionDatos = new DescripcionDatos();
        return $descripcionDatos;
    }

    public function save(DescripcionDatos $descripcionDatos): DescripcionDatos
    {
        $this->em->persist($descripcionDatos);
        $this->em->flush();
        return $descripcionDatos;
    }

    public function reload(DescripcionDatos $descripcionDatos): DescripcionDatos
    {
        $this->em->refresh($descripcionDatos);
        return $descripcionDatos;
    }

    public function delete(DescripcionDatos $descripcionDatos)
    {
        $this->em->remove($descripcionDatos);
        $this->em->flush();
    }
}