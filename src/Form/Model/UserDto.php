<?php

namespace App\Form\Model;

use App\Entity\User;
use DateTime;

/*
 * Descripción: Es la clase dto del usuario  
 *              El objeto que serializa los datos del la llamada JSON     
 */

class UserDto
{
    public $username;
    public $password;
    public $roles;

 
    public function __construct()
    {
 
    }
    
    public static function createFromUser(User $user): self
    {
        $dto = new self();
        $dto->username =  $user->getUsername();
        $dto->password =  $user->getPassword();
        $dto->roles =  $user->getRoles();
        return $dto;
    }
}

