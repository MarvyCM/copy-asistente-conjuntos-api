<?php

namespace App\Service\Manager;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;


/*
 * Descripción: Es el repositorio del usuario
 *              realiza las operaciones de persistencia sobre el ORM
*/

class UserManager
{

    private $em;
    private $userRepository;


    public function __construct(EntityManagerInterface $em, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
    }

    public function find(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function findByUserName($username)
    {
        return $this->userRepository->findByUserName($username);
    }


    public function create(): User
    {
        $user = new User();
        return $user;
    }

    public function save(User $user): User
    {
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    public function reload(User $user): User
    {
        $this->em->refresh($user);
        return $user;
    }
}