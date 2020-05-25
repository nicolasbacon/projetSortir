<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Sortie;
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
        $researchForm = $this->createForm(ResearchType::class);

        $researchForm->handleRequest($request);

        $organisateur = $request->get('organisateur');
        var_dump($organisateur);
        $incrit = $request->get('inscrit');
        $nonInscrit = $request->get('nonInscrit');
        $sortiePasse = $request->get('sortiePasse');

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

        $sorties = [];

        if ($user != null && $campus==null) {
            $sorties += $sortieRepo->findByCampus($user->getCampus()->getId());
        }
        if ($organisateur !=null){
            $sorties=($sortieRepo->findByOrganisateur($organisateur));
        }
        if ($incrit !=null){
            $sorties += $sortieRepo->findByParticipant($incrit);
        }
        if ($nonInscrit !=null){
            $sorties += $sortieRepo->findByNonInscrit($nonInscrit);
        }
        if ($sortiePasse !=null){
            $sorties += $sortieRepo->findBySortiePasse();

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
