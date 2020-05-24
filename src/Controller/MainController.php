<?php

namespace App\Controller;

use App\Entity\Campus;
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
        $sortieRepo = $em->getRepository(Sortie::class);
        $campusRepo = $em->getRepository(Campus::class);
        $user = $this->getUser();
        if ($user != null) {
            $sorties = $sortieRepo->findByCampus($user->getCampus()->getId());
        }
       else{
           $sorties = $sortieRepo->findAll();
       }
        $allCampus = $campusRepo->findAll();

        return $this->render('main/accueil.html.twig', [
            'controller_name' => 'MainController',
            "sorties" => $sorties,
            "allCampus" => $allCampus,
        ]);
    }
}
