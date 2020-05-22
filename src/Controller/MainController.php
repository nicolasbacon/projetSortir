<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(EntityManagerInterface $em, Request $request)
    {
        $user = $this->getUser();
        $sortieRepo = $em->getRepository(Sortie::class);
        $campusRepo = $em->getRepository(Campus::class);

        $sorties = $sortieRepo->findByCampus($user->getCampus()->getId());
        $allCampus = $campusRepo->findAll();

        return $this->render('main/accueil.html.twig', [
            'controller_name' => 'MainController',
            "sorties" => $sorties,
            "allCampus" => $allCampus,
        ]);
    }
}
