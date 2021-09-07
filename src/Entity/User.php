<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Table('user')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity('email')]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue(strategy: 'AUTO')]
	
    private $id;
    
    #[ORM\Column(type: 'string', unique: true)]
	#[Assert\Length(max: 25)]
	#[Assert\NotBlank(message: "Vous devez saisir un nom d'utilisateur.")]
	
    private $username;
	
	#[ORM\Column(type: 'json')]
	
	private $roles = [];
   
    #[ORM\Column(type: 'string')]
	#[Assert\Length(max: 64)]
	
    private $password;
   
    #[ORM\Column(type: 'string', unique: true)]
	#[Assert\Length(max: 60)]
	#[Assert\NotBlank(message: 'Vous devez saisir une adresse email.')]
	#[Assert\Email(message: "Le format de l'adresse n'est pas correct.")]
	
    private $email;

    public function getId(): ?int
    {
        return $this->id;
    }
	
	/**
	 * A visual identifier that represents this user.
	 *
	 * @see UserInterface
	 */
	public function getUserIdentifier(): string
	{
		return (string) $this->username;
	}

    public function setUsername(string $username): self
    {
		$this->username = $username;
		return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }
    
	/**
	 * @see PasswordAuthenticatedUserInterface
	 */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
		$this->password = $password;
		return $this;
    }

    public function getEmail(): string
    {
        return (string) $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }
	
	/**
	 * @see UserInterface
	 */
	public function getRoles(): array
	{
		$roles = $this->roles;
		// guarantee every user at least has ROLE_USER
		$roles[] = 'ROLE_USER';
		
		return array_unique($roles);
	}
	
	public function setRoles(array $roles): self
	{
		$this->roles = $roles;
		return $this;
	}

    public function eraseCredentials()
    {
    }
	
	/**
	 * @deprecated since Symfony 5.3, use getUserIdentifier instead
	 */
	public function getUsername(): string
	{
		return (string) $this->username;
	}
}
