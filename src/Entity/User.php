<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Hateoas\Relation(
    'self',
    href: new Hateoas\Route(name: 'user', parameters: ['id' => 'expr(object.getId())']),
    attributes: ["method" => "GET"],
    exclusion: new Hateoas\Exclusion(groups: ['read:user'])
)]
#[Hateoas\Relation(
    'update',
    href: new Hateoas\Route(name: 'updateUser', parameters: ['id' => 'expr(object.getId())']),
    attributes: ["method" => "PUT"],
    exclusion: new Hateoas\Exclusion(groups: ['read:user'])
)]
#[Hateoas\Relation(
    'resetPassword',
    href: new Hateoas\Route(name: 'resetPassword', parameters: ['id' => 'expr(object.getId())']),
    attributes: ["method" => "POST"],
    exclusion: new Hateoas\Exclusion(groups: ['read:user'])
)]
#[Hateoas\Relation(
    'updateRoles',
    href: new Hateoas\Route(name: 'updateRoles', parameters: ['id' => 'expr(object.getId())']),
    attributes: ["method" => "PUT"],
    exclusion: new Hateoas\Exclusion(groups: ['read:user'], excludeIf: 'expr(not is_granted("ROLE_SUPER_ADMIN"))')
)]
#[Hateoas\Relation(
    'updateCustomer',
    href: new Hateoas\Route(name: 'updateCustomer', parameters: ['id' => 'expr(object.getId())']),
    attributes: ["method" => "PUT"],
    exclusion: new Hateoas\Exclusion(groups: ['read:user'], excludeIf: 'expr(not is_granted("ROLE_SUPER_ADMIN"))')
)]
#[Hateoas\Relation(
    'delete',
    href: new Hateoas\Route(name: 'deleteUser', parameters: ['id' => 'expr(object.getId())']),
    attributes: ["method" => "DELETE"],
    exclusion: new Hateoas\Exclusion(groups: ['read:user'], excludeIf: 'expr(not is_granted("ROLE_SUPER_ADMIN") and not is_granted("ROLE_ADMIN"))')
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:user', 'read:customer'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['create:user', 'read:user'])]
    #[Assert\NotBlank()]
    #[Assert\Length(
        min: 2,
        max: 256,
        minMessage: 'Your first name must be at least {{ limit }} characters long',
        maxMessage: 'Your first name cannot be longer than {{ limit }} characters',
    )]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Groups(['create:user', 'read:user'])]
    #[Assert\NotBlank()]
    #[Assert\Length(
        min: 2,
        max: 256,
        minMessage: 'Your last name must be at least {{ limit }} characters long',
        maxMessage: 'Your last name cannot be longer than {{ limit }} characters',
    )]
    private ?string $lastname = null;

    #[ORM\Column(length: 180)]
    #[Groups(['create:user', 'read:user', 'read:customer'])]
    #[Assert\NotBlank()]
    #[Assert\Email()]
    #[Assert\Length(
        min: 2,
        max: 256,
        minMessage: 'Your email must be at least {{ limit }} characters long',
        maxMessage: 'Your email cannot be longer than {{ limit }} characters',
    )]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['read:user', 'read:customer'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank()]
    private ?string $password = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:user'])]
    private ?Customer $customer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
    
    /**
     * Méthode getUsername qui permet de retourner le champ qui est utilisé pour l'authentification.
     *
     * @return string
     */
    public function getUsername(): string {
        return $this->getUserIdentifier();
    }

    /**
     * @see UserInterface
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }
}
