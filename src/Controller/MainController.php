<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Sortie;
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
        $campus = $request->get('campus');

        $organisateur = $request->get('organisateur');
        var_dump($organisateur);
        $incrit = $request->get('inscrit');
        $nonInscrit = $request->get('nonInscrit');
        $sortiePasse = $request->get('sortiePasse');

        $sortieRepo = $em->getRepository(Sortie::class);
        $campusRepo = $em->getRepository(Campus::class);
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
        }
       else{
           $sorties = $sortieRepo->findAll();
       }
        $allCampus = $campusRepo->findAll();

        return $this->render('main/accueil.html.twig', [
            'controller_name' => 'MainController',
            "sorties" => $sorties,
            "allCampus" => $allCampus,
            "campus" => $campus,
        ]);
    }
}
