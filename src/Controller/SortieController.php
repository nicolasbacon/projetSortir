<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\DesinscritType;
use App\Form\InscritType;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{

    /**
     * @Route("/sortie/{id}", name="sortie_detail", requirements={"id": "\d+"})
     */
    public function detail($id, EntityManagerInterface $em, Request $request)
    {
        //récupérer la sortie en BDD:
        $sortieRepository = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepository->find($id);

        //form inscrit
        $inscritForm = $this->createForm(InscritType::class, $sortie);
        $inscritForm->handleRequest($request);

        //form desinscrit
        $desinscritForm = $this->createForm(DesinscritType::class, $sortie);
        $desinscritForm->handleRequest($request);

        $user = $this->getUser();

        if (empty($sortie)){
            throw $this->createNotFoundException("Cette sortie n'existe pas!");
        }
        //soumettre l'incription
        if ($inscritForm->isSubmitted()){
            $sortie->addParticipant($user);
            $em->persist($sortie);
            $em->flush();
            $this->addFlash('success', 'Vous êtes incrit à la sortie !');
        }

        //soumettre la désinscription
        if ($desinscritForm->isSubmitted()){
            $sortie->removeParticipant($user);
            $em->persist($sortie);
            $em->flush();
            $this->addFlash('success', 'Vous êtes désinscrit de la sortie !');
        }

        return $this->render('sortie/sortie.html.twig', [
            "sortie" => $sortie,
            "user" => $user,
            'inscritForm' => $inscritForm->createView(),
            'desinscritForm' => $desinscritForm->createView(),
        ]);
    }


    /**
     * @Route("/sortie/add", name="add_sortie")
     */
    public function addSortie(EntityManagerInterface $em, Request $request)
    {
        $lieux = $em->getRepository(Lieu::class)->findAll();

        //@todo : traiter le formulaire

        $etat = new Etat();
        $etat->setLibelle('Cree');

        $sortie = new Sortie();
        $sortie->setEtat($etat);

        $sortie->setOrganisateur($this->getUser());

        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        if($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $em->persist($sortie);
            $em->flush();
            $this->addFlash('success', 'La sortie a été ajoutée !');
            return $this->redirectToRoute('add_sortie');
        }
        return $this->render('sortie/add.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            'lieux' => $lieux,
        ]);
    }


    /**
     * @Route("/modifierSortie/{id}", name="modifier_sortie", requirements={"id": "\d+"},
     *     methods={"GET"})
     */
    public function modifierSortie($id, Request $request)
    {
        //récupérer la sortie en BDD:
        $sortieRepository = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepository->find($id);

        if (empty($sortie)){
            throw $this->createNotFoundException("Cette sortie n'existe pas!");
        }

        return $this->render('sortie/modifierSortie.html.twig', [
            "sortie" => $sortie
        ]);
    }

    /**
     * @Route("/annulerSortie/{id}", name="annuler_sortie", requirements={"id": "\d+"},
     *     methods={"GET"})
     */
    public function annulerSortie($id, Request $request)
    {
        //récupérer la sortie en BDD:
        $sortieRepository = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepository->find($id);

        if (empty($sortie)){
            throw $this->createNotFoundException("Cette sortie n'existe pas!");
        }

        return $this->render('sortie/annulerSortie.html.twig', [
            "sortie" => $sortie
        ]);
    }


}
