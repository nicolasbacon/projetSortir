<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/admin")
 */
class VilleController extends AbstractController
{
    /**
     * @Route("/ville", name="ville")
     */
    public function index(EntityManagerInterface $em, Request $request)
    {
        $villes = $em->getRepository(Ville::class)->findAll();

        $ville = new Ville();
        $villeForm = $this->createForm(VilleType::class, $ville);

        $villeForm->handleRequest($request);

        if($villeForm->isSubmitted() && $villeForm->isValid()) {
            $em->persist($ville);
            $em->flush();
            $this->addFlash('success', 'La ville a bien été ajouté !');
            return $this->redirectToRoute('ville');
        }

        return $this->render('ville/index.html.twig', [
            'controller_name' => 'VilleController',
            'villes' => $villes,
            'villeForm' => $villeForm->createView(),
        ]);
    }
}
