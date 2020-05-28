<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity(fields={"username"})
 * @UniqueEntity(fields={"mail"})
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class Participant implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Veuillez remplir le champ pseudonyme")
     * @Assert\Length(max=255, maxMessage="255 charactères maximum !")
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $username;

    /**
     * @Assert\NotBlank(message="Veuillez remplir le champ nom")
     * @Assert\Length(max=255, maxMessage="255 charactères maximum !")
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @Assert\NotBlank(message="Veuillez remplir le champ prénom")
     * @Assert\Length(max=255, maxMessage="255 charactères maximum !")
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @Assert\Length(max=10, maxMessage="10 chiffres maximum !")
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $telephone;


    /**
     * @Assert\NotBlank(message="Veuillez remplir le champ e-mail")
     * @Assert\Length(max=255, maxMessage="255 charactères maximum !")
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $mail;

    /**
     * @Assert\NotBlank(message="Veuillez remplir le champ mot de passe")
     * @Assert\Length(max=255, maxMessage="255 charactères maximum !")
     * @ORM\Column(type="string", length=255)
     */
    private $motPasse;

    /**
     * @ORM\Column(type="boolean")
     */
    private $administrateur;

    /**
     * @ORM\Column(type="boolean")
     */
    private $actif;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $campus;

    /**
     * @ORM\ManyToMany(targetEntity=Sortie::class, mappedBy="participants")
     * @ORM\JoinColumn(onDelete="cascade")
     */
    private $sorties;
    /**
     * @ORM\Column(type="string")
     */
    //@ORM\OneToOne(targetEntity="Image", cascade={"persist", "remove"})
    private $imageFilename;

    public function getImageFilename()
    {
        return $this->imageFilename;
    }

    public function setImageFilename($imageFilename)
    {
        $this->imageFilename = $imageFilename;

        return $this;
    }

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom(string $nom)
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone()
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone)
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function setMail(string $mail)
    {
        $this->mail = $mail;

        return $this;
    }

    public function getMotPasse()
    {
        return $this->motPasse;
    }


    public function setMotPasse($motPasse): void
    {
        $this->motPasse = $motPasse;
    }


    public function getAdministrateur()
    {
        return $this->administrateur;
    }

    public function setAdministrateur($administrateur)
    {
        $this->administrateur = $administrateur;

        return $this;
    }

    public function getActif()
    {
        return $this->actif;
    }

    public function setActif( $actif)
    {
        $this->actif = $actif;

        return $this;
    }

    public function getCampus()
    {
        return $this->campus;
    }

    public function setCampus( $campus)
    {
        $this->campus = $campus;

        return $this;
    }

    public function getRoles()
    {
        if ($this->getAdministrateur()) {
            return ["ROLE_ADMIN"];
        }else{
            return ["ROLE_USER"];
        }
    }

    public function getPassword()
    {
        return $this->motPasse;
    }

    public function setPassword($motPasse)
    {
        $this->motPasse = $motPasse;

        return $this;
    }
    public function getSalt()
    {
        return null;
    }


    public function eraseCredentials(){}


    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;

    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection|Sortie[]
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(Sortie $sorty): self
    {
        if (!$this->sorties->contains($sorty)) {
            $this->sorties[] = $sorty;
            $sorty->addParticipant($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): self
    {
        if ($this->sorties->contains($sorty)) {
            $this->sorties->removeElement($sorty);
            $sorty->removeParticipant($this);
        }

        return $this;
    }



}
