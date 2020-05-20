<?php

namespace App\Controller;

use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(EntityManagerInterface $em)
    {
        $sorties = $em->getRepository(Sortie::class)->findAll();


        return $this->render('main/accueil.html.twig', [
            'controller_name' => 'MainController',
            "sorties" => $sorties,
        ]);
    }
}
