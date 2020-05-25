<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Sortie;
use App\Form\ResearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use function Sodium\add;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(EntityManagerInterface $em, Request $request)
    {
        $researchForm = $this->createForm(ResearchType::class);

        $researchForm->handleRequest($request);

        // Recuperation du formulaire de recherche
        /**
        $campus = $request->request->get('campus');
        $researh = $request->request->get('researh');
        $dateDebut = $request->request->get('dateDebut');
        $dateFin = $request->request->get('dateFin');
        **/

        //Repository
        $sortieRepo = $em->getRepository(Sortie::class);
        $campusRepo = $em->getRepository(Campus::class);

        //Recuperation du user en session
        $user = $this->getUser();

        $campus = null;

        if($researchForm->isSubmitted() && $researchForm->isValid()) {
            $campus = $researchForm->get('campus')->getData();

            $sorties = $sortieRepo->findByCampus($campus->getId());

        }
        //Si il est connecter
        else if ($user != null) {
            //On recupere les sorties de son campus
            $sorties = $sortieRepo->findByCampus($user->getCampus()->getId());
        }
       else {
           //On recupere toute les sorties
           $sorties = $sortieRepo->findAll();
       }
        $allCampus = $campusRepo->findAll();

        return $this->render('main/accueil.html.twig', [
            'controller_name' => 'MainController',
            "sorties" => $sorties,
            "allCampus" => $allCampus,
            "researchForm" => $researchForm->createView(),
            'campus' => $campus,
        ]);
    }
}
