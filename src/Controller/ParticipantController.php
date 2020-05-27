<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ParticipantController extends AbstractController
{

    /**
     * @Route("/login", name="login")
     */
    public function login()
    {
        return $this->render('participant/login.html.twig', []);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(){}


    /**
     * @Route("/participant/detail/{id}", name="participant_detail",
     *     requirements={"id":"\d+"})
     */
    public function detail(EntityManagerInterface $em, $id)
    {
        $repo = $em->getRepository(Participant::class);

        $participant = $repo->find($id);

        return $this->render('participant\afficher.html.twig',[
            "participant" => $participant,
        ]);
    }

    /**
     * @Route("/participant/modifier", name="participant_modifier")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function modifierProfil(
       UserPasswordEncoderInterface $passwordEncoder, Request $request, EntityManagerInterface $em)
    {


        $participant = $this->getUser();
        $registerForm = $this->createForm(ParticipantType::class, $participant);


        $registerForm->handleRequest($request);

        if ($registerForm->isSubmitted() && $registerForm->isValid()) {
            $participant->setActif(true);


            $password = $passwordEncoder->encodePassword($participant, $participant->getPassword());
            $participant->setPassword($password);


            $em->persist($participant);
            $em->flush();

            $this->addFlash("success", "Votre profil a été modifié avec succès !");
            return $this->redirectToRoute("home");

        }
        return $this->render('participant/modifier.html.twig', ["registerForm"=>$registerForm->createView()]);
    }

    /**
     * @Route("/admin/register", name="register")
     */
    public function register(Request $request,
                             EntityManagerInterface $em,
                             UserPasswordEncoderInterface $encoder)
    {
        $user = new Participant();

        $registerForm = $this->createForm(RegisterType::class, $user);
        $registerForm->handleRequest($request);

        if ($registerForm->isSubmitted() && $registerForm->isValid()) {
            //hasher le mot de passe
            $hashed = $encoder->encodePassword($user,$user->getPassword());
            $user->setPassword($hashed);

            $em->persist($user);
            $em->flush();
        }

        return $this->render("participant/register.html.twig",[
            'registerForm' => $registerForm->createView()
        ]);
    }


    /**
     * @Route("/admin/allProfils", name="all_participant")
     */
    public function allProfils(EntityManagerInterface $em)
    {

        $participants = $em->getRepository(Participant::class)->findAll();

        return $this->render('participant/allProfils.html.twig',[
            "participants" => $participants,
        ]);
    }

    /**
     * @Route("/admin/allProfils", name="all_participant")
     */
    public function modifEtatActif(EntityManagerInterface $em)
    {

        $participants = $em->getRepository(Participant::class)->findAll();

        return $this->render('participant/allProfils.html.twig',[
            "participants" => $participants,
        ]);
    }

}
