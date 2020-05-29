<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\CSVType;
use App\Form\ParticipantType;
use App\Form\RegisterType;
use App\Form\ResetPassType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

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

    /**
     * @Route("/oubli-pass", name="app_forgotten_password")
     */
    public function oubliPass(EntityManagerInterface $em,Request $request, UserRepository $users, \Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator
    ): \Symfony\Component\HttpFoundation\Response
    {
        // On initialise le formulaire
        $form = $this->createForm(ResetPassType::class);

        // On traite le formulaire
        $form->handleRequest($request);

        // Si le formulaire est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère les données
            $donnees = $form->getData();

            // On cherche un utilisateur ayant cet e-mail
            $participants = $em->getRepository(Participant::class)->findByMail($donnees['email']);
            $participant = $participants[0];

            // Si l'utilisateur n'existe pas
            if ($participant === null) {
                // On envoie une alerte disant que l'adresse e-mail est inconnue
                $this->addFlash('danger', 'Cette adresse e-mail est inconnue');

                // On retourne sur la page de connexion
                return $this->redirectToRoute('login');
            }

            // On génère un token
            $token = $tokenGenerator->generateToken();

            // On essaie d'écrire le token en base de données
            try{
                $participant->setResetToken($token);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($participant);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('login');
            }

            $transport = (new \Swift_SmtpTransport('smtp.gmail.com', 465, 'ssl'))
                ->setUsername('nicolasbacon.nb@gmail.com')
                ->setPassword('uphajnxisbhreqkj')
            ;

            // Create the Mailer using your created Transport
            $mailer = new \Swift_Mailer($transport);

            // On génère l'URL de réinitialisation de mot de passe
            $url = $this->generateUrl('app_reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

            // On génère l'e-mail
            $message = (new \Swift_Message('Mot de passe oublié'))
                ->setFrom(['nicolasbacon.nb@gmail.com' => 'Nicolas BACON'])
                ->setTo([$participant->getMail()])
                ->setBody(
                    "Bonjour,<br><br>Une demande de réinitialisation de mot de passe a été effectuée. Veuillez cliquer sur le lien suivant : " . $url,
                    'text/html'
                )
            ;

            try{
                // On envoie l'e-mail
                $mailer->send($message);

                $transport = new \Swift_SendmailTransport('/usr/sbin/sendmail -bs');
                // On crée le message flash de confirmation
                $this->addFlash('message', 'E-mail de réinitialisation du mot de passe envoyé !');

            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());

            } finally {
                // On redirige vers la page de login
                return $this->redirectToRoute('login');
            }

        }

        // On envoie le formulaire à la vue
        return $this->render('security/forgotten_password.html.twig',['emailForm' => $form->createView()]);
    }

    /**
     * @Route("/reset_pass/{token}", name="app_reset_password")
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder)
    {
        // On cherche un utilisateur avec le token donné
        $user = $this->getDoctrine()->getRepository(Participant::class)->findOneBy(['reset_token' => $token]);

        // Si l'utilisateur n'existe pas
        if ($user === null) {
            // On affiche une erreur
            $this->addFlash('danger', 'Token Inconnu');
            return $this->redirectToRoute('login');
        }

        // Si le formulaire est envoyé en méthode post
        if ($request->isMethod('POST')) {
            // On supprime le token
            $user->setResetToken(null);

            // On chiffre le mot de passe
            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));

            // On stocke
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // On crée le message flash
            $this->addFlash('message', 'Mot de passe mis à jour');

            // On redirige vers la page de connexion
            return $this->redirectToRoute('login');
        }else {
            // Si on n'a pas reçu les données, on affiche le formulaire
            return $this->render('security/reset_password.html.twig', ['token' => $token]);
        }

    }

}
