<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\CSVType;
use App\Form\ParticipantType;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
    public function logout()
    {
        $this->addFlash('sucess', 'Mauvais mot de passe !');
    }


    /**
     * @Route("/participant/detail/{id}", name="participant_detail",
     *     requirements={"id":"\d+"})
     */
    public function detail(EntityManagerInterface $em, $id)
    {
        $repo = $em->getRepository(Participant::class);

        $participant = $repo->find($id);

        return $this->render('participant\afficher.html.twig', [
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

            //gestion de l'image
            $brochureFile = $registerForm->get('image')->getData();
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();
                try {
                    $brochureFile->move(
                        $this->getParameter('image_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {

                }
                $participant->setImageFilename($newFilename);
            }
           /* $participant->setImageFilename(
                new File($this->getParameter('image_directory').'/'.$participant->getBrochureFilename())
            );*/
            //{{ form_row(registerForm.image) }}

            $password = $passwordEncoder->encodePassword($participant, $participant->getPassword());
            $participant->setPassword($password);


            $em->persist($participant);
            $em->flush();

            $this->addFlash("success", "Votre profil a été modifié avec succès !");
            return $this->redirectToRoute("home");

        }
        return $this->render('participant/modifier.html.twig', [
            "registerForm" => $registerForm->createView(),
            "participant" => $participant,
            ]);
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
            $hashed = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hashed);

            $em->persist($user);
            $em->flush();
            $this->addFlash("success", "Le nouveau profil a été créé avec succès !");
            return $this->redirectToRoute("home");
        }

        return $this->render("participant/register.html.twig", [
            'registerForm' => $registerForm->createView()
        ]);
    }


    /**
     * @Route("/admin/allProfils", name="all_participant")
     */
    public function allProfils(EntityManagerInterface $em, Request $request)
    {

        $participants = $em->getRepository(Participant::class)->findAll();

        $CSVForm = $this->createForm(CSVType::class);

        $CSVForm->handleRequest($request);

        if ($CSVForm->isSubmitted() && $CSVForm->isValid()) {
            $file = $CSVForm->get('csv')->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('csv_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {

                }

                //load the CSV document from a file path
                $csv = Reader::createFromPath('../public/csv/'.$newFilename, 'r');
                $csv->setHeaderOffset(0);

                $records = $csv->getRecords(); //returns all the CSV records as an Iterator object

                $campus = $em->getRepository(Campus::class)->findAll();


                foreach ($records as $record) {

                    $participant = new Participant();
                    $participant
                        ->setCampus($campus[intval($record['campus_id'])-1])
                        ->setUsername($record['username'])
                        ->setNom($record['nom'])
                        ->setPrenom($record['prenom'])
                        ->setTelephone($record['telephone'])
                        ->setMail($record['mail'])
                        ->setMotPasse($record['mot_passe'])
                    ;

                    $participant->setAdministrateur(intval($record['administrateur']));
                    $participant->setActif(intval($record['actif']));

                    $em->persist($participant);
                }

                $em->flush();
                $records = null;
                $csv = null;
                if( file_exists ( '../public/csv/'.$newFilename))
                    unlink( '../public/csv/'.$newFilename ) ;

            }


            }

        return $this->render('participant/allProfils.html.twig', [
            "participants" => $participants,
            "CSVForm" => $CSVForm->createView(),
        ]);
    }

    /**
     * @Route("/admin/activer/{id}", name="active_participant", requirements={"id":"\d+"})
     */
    public function activeParticipant(EntityManagerInterface $em, $id)
    {

        $participant = $em->getRepository(Participant::class)->find($id);
        if ($participant->getActif() == false) {
            $participant->setActif(true);
            $em->persist($participant);
            $em->flush();
            $this->addFlash("success", "Le profil a été activé avec succès !");
            return $this->redirectToRoute('all_participant');
        } else {
            $this->addFlash("error", "Le profil a déjà été activé !");
            return $this->redirectToRoute('all_participant');
        }


    }

    /**
     * @Route("/admin/desactiver/{id}", name="desactive_participant", requirements={"id":"\d+"})
     */
    public function desactiveParticipant(EntityManagerInterface $em, $id)
    {

        $participant = $em->getRepository(Participant::class)->find($id);
        if ($participant->getActif() == true) {
            $participant->setActif(false);
            $em->persist($participant);
            $em->flush();
            $this->addFlash("success", "Le profil a été désactivé avec succès !");
            return $this->redirectToRoute('all_participant');
        } else {
            $this->addFlash("error", "Le profil a déjà été désactivé !");
            return $this->redirectToRoute('all_participant');
        }

    }

    /**
     * @Route("/admin/supprimer/{id}", name="supprime_participant", requirements={"id":"\d+"})
     */
    public function supprimeParticipant(EntityManagerInterface $em, $id)
    {

        $participant = $em->getRepository(Participant::class)->find($id);
        $sorties = $em->getRepository(Sortie::class)->findByOrganisateur($participant);

        foreach ($sorties as $sortie) {
            $em->remove($sortie);
        }

        $em->remove($participant);
        $em->flush();
        $this->addFlash("success", "Le profil a été supprimé avec succès !");


        return $this->redirectToRoute('all_participant');

    }


}
