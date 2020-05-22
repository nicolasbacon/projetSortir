<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class CampusController extends AbstractController
{
    /**
     * @Route("/campus", name="campus")
     */
    public function index(EntityManagerInterface $em, Request $request)
    {
        $allCampus = $em->getRepository(Campus::class)->findAll();

        $campus = new Campus();
        $campusForm = $this->createForm(CampusType::class, $campus);

        $campusForm->handleRequest($request);

        if($campusForm->isSubmitted() && $campusForm->isValid()) {
            $em->persist($campus);
            $em->flush();
            $this->addFlash('success', 'Le campus a bien été ajouté !');
            return $this->redirectToRoute('campus');
        }

        return $this->render('campus/index.html.twig', [
            'controller_name' => 'CampusController',
            'campus' => $allCampus,
            'campusForm' => $campusForm->createView(),
        ]);
    }
}
