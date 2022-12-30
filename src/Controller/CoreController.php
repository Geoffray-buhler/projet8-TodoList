<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CoreController extends AbstractController
{
    protected EntityManagerInterface $emi;
    protected UserPasswordHasherInterface $pswEncoder;

    public function __construct( EntityManagerInterface $emi, UserPasswordHasherInterface $pswEncoder)
    {
        $this->emi = $emi;
        $this->pswEncoder = $pswEncoder;
    }
}
