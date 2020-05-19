<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/register" name="user_register")
     */
    public function register(Request $request, EntityManagerInterface $em)
    {
        $user = new User();
        $registerForm = $this->createForm(RegisterType::class);

        $registerForm->handleRequest($request);

        if ($registerForm->isValid() && $registerForm->isSubmitted()) {

        }
        return $this->render('user/register.html.twig', []);
    }

    /**
     * @Route("/login", name="login")
     */
    public function login()
    {


        return $this->render('user/login.html.twig', []);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(){}
}
