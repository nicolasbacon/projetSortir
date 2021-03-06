<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\ResearchType;
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

        //Formulaire de recherche
        $researchForm = $this->createForm(ResearchType::class);
        $researchForm->handleRequest($request);

        //Repository
        $sortieRepo = $em->getRepository(Sortie::class);
        $campusRepo = $em->getRepository(Campus::class);

        //Recuperation du user en session
        $user = $this->getUser();

        if($researchForm->isSubmitted() && $researchForm->isValid()) {
            //Recupere les champs du formulaire
            $campus = $researchForm->get('campus')->getData();
            $research = strtolower($researchForm->get('research')->getData());
            $dateDebut = $researchForm->get('dateDebut')->getData();
            $dateFin = $researchForm->get('dateFin')->getData();

            $organisateur = $researchForm->get('organisateur')->getData();
            $incrit = $researchForm->get('inscrit')->getData();
            $nonInscrit = $researchForm->get('nonInscrit')->getData();
            $sortiePasse = $researchForm->get('sortiePasse')->getData();

            //Recherche en fonction du campus
            $sorties = $sortieRepo->findByCampus($campus->getId());

            //Recherche avec la zone de texte si elle n'est pas vide
            if (!empty($research)) {
                foreach ($sorties as $key => $sortie) {
                    if(!preg_match('#'.$research.'#', strtolower($sortie->getNom())) ) unset($sorties[$key]);
                }
            }
            //Recherche par la date
            if ($dateDebut != null) {
                foreach ($sorties as $key => $sortie) {
                    if($dateDebut > $sortie->getDateHeureDebut()) unset($sorties[$key]);
                }
            }
            if ($dateFin != null) {
                foreach ($sorties as $key => $sortie) {
                    if(  $dateFin < $sortie->getDateHeureDebut())  unset($sorties[$key]);
                }
            }
            //Verifie si organisateur
            if ($organisateur){
                foreach ($sorties as $key => $sortie) {
                    if ($user != $sortie->getOrganisateur()) unset($sorties[$key]);
                }
            }
            if ($incrit){
                foreach ($sorties as $key => $sortie) {
                    $participe = false;
                    foreach ($sortie->getParticipants() as $participant) {
                        if ($user == $participant) $participe = true;
                    }
                    if ($participe == false) unset($sorties[$key]);
                }
            }
            if ($nonInscrit){
                foreach ($sorties as $key => $sortie) {
                    $participe = false;
                    foreach ($sortie->getParticipants() as $participant) {
                        if ($user == $participant) $participe = true;
                    }
                    if ($participe == true) unset($sorties[$key]);
                }
            }
            if ($sortiePasse) {
                $now = new \DateTime();
                foreach ($sorties as $key => $sortie) {
                    if ($now > $sortie->getDateHeureDebut()) unset($sorties[$key]);
                }
            }
        }
        //Si il est connecté
        else if ($user != null) {
            //On recupere les sorties de son campus
            $sorties = $sortieRepo->findByCampus($user->getCampus()->getId());
        }
       else {
           //On recupere toute les sorties
           $sorties = $sortieRepo->findAll();
           //Modif etat des sorties
           $etatRepo = $em->getRepository(Etat::class);
           $etats = $etatRepo->findAll();
           foreach ($sorties as $sortie) {
               //Etat Ferme
               if ($sortie->getDateLimiteInscription() <= new \DateTime()) $sortie->setEtat($etats[2]);
               //Etat En cour
               $dateFinSortie = new \DateTime();
               $dateFinSortie->setTimestamp($sortie->getDateHeureDebut()->getTimestamp() + $sortie->getDuree()->getTimestamp() + 3600);

               if ($sortie->getDateHeureDebut() <= new \DateTime() && $dateFinSortie->getTimestamp() >= time()) $sortie->setEtat($etats[3]);
               //Etat Termine
               $dateFinSortie = new \DateTime();
               $dateFinSortie->setTimestamp($sortie->getDateHeureDebut()->getTimestamp() + $sortie->getDuree()->getTimestamp() + 3600);
               if ($dateFinSortie->getTimestamp() <= time()) $sortie->setEtat($etats[5]);
               //Etat Archiver
               $dateArchivage = new \DateTime(date_format($sortie->getDateHeureDebut(), 'Y-m-d H:i:s'));
               $dateArchivage->add(new \DateInterval('P30D'));
               if ($dateArchivage <= new \DateTime()) $sortie->setEtat($etats[6]);

               $em->persist($sortie);
           }
           $em->flush();

       }
        $allCampus = $campusRepo->findAll();

        return $this->render('main/accueil.html.twig', [
            "sorties" => $sorties,
            "allCampus" => $allCampus,
            "researchForm" => $researchForm->createView(),
        ]);
    }
}
