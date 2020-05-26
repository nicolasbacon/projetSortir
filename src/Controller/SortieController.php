<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\SortieAnnuleeType;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{

    /**
     * @Route("/sortie/{id}", name="sortie_detail", requirements={"id": "\d+"},
     *     methods={"GET"})
     */
    public function detail($id, Request $request)
    {
        //récupérer la sortie en BDD:
        $sortieRepository = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepository->find($id);

        if (empty($sortie)){
            throw $this->createNotFoundException("Cette sortie n'existe pas!");
        }

        return $this->render('sortie/sortie.html.twig', [
            "sortie" => $sortie
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
     * @Route("/modifierSortie/{id}", name="modifier_sortie", requirements={"id": "\d+"})
     */
    public function modifierSortie($id, Request $request, EntityManagerInterface $em)
    {
        //récupérer la sortie en BDD:
        $sortieRepository = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepository->find($id);

        $sortieModifForm = $this->createForm(SortieType::class, $sortie);
        $sortieModifForm->handleRequest($request);

        if (empty($sortie)){
            throw $this->createNotFoundException("Cette sortie n'existe pas!");
        }
        else {
            if($sortieModifForm->isSubmitted() && $sortieModifForm->isValid()) {
                $etat = new Etat();
                $etat->setLibelle('Publiée');
                $sortie->setEtat($etat);

                $em->persist($sortie);
                $em->flush();
            }
        }
        return $this->render('sortie/modifierSortie.html.twig', [
            "sortie" => $sortie,
            'sortieModifForm' => $sortieModifForm->createView(),
        ]);
    }

    /**
     * @Route("/annulerSortie/{id}", name="annuler_sortie", requirements={"id": "\d+"})
     */
    public function annulerSortie($id, Request $request, EntityManagerInterface $em)
    {
        //récupérer la sortie en BDD:
        $sortieRepository = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepository->find($id);

        $sortieAnulForm = $this->createForm(SortieAnnuleeType::class, $sortie);
        $sortieAnulForm->handleRequest($request);

        if (empty($sortie)){
            throw $this->createNotFoundException("Cette sortie n'existe pas!");
        }
        else {
            if($sortieAnulForm->isSubmitted() && $sortieAnulForm->isValid()) {
                $etat = new Etat();
                $etat->setLibelle('Annulée');
                $sortie->setEtat($etat);

                $em->persist($sortie);
                $em->flush();
            }
        }

        return $this->render('sortie/annulerSortie.html.twig', [
            'sortie' => $sortie,
            'sortieAnulForm' => $sortieAnulForm->createView(),
        ]);
    }

}
