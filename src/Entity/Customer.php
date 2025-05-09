<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[Hateoas\Relation(
    'self',
    href: new Hateoas\Route(name: 'customer', parameters: ['id' => 'expr(object.getId())']),
    attributes: ["method" => "GET"],
    exclusion: new Hateoas\Exclusion(groups: ['read:customer'])
)]
#[Hateoas\Relation(
    'update',
    href: new Hateoas\Route(name: 'updateCustomer', parameters: ['id' => 'expr(object.getId())']),
    attributes: ["method" => "PUT"],
    exclusion: new Hateoas\Exclusion(groups: ['read:customer'], excludeIf: 'expr(not is_granted("ROLE_SUPER_ADMIN") and not is_granted("ROLE_ADMIN"))')
)]
#[Hateoas\Relation(
    'delete',
    href: new Hateoas\Route(name: 'deleteCustomer', parameters: ['id' => 'expr(object.getId())']),
    attributes: ["method" => "DELETE"],
    exclusion: new Hateoas\Exclusion(groups: ['read:customer'], excludeIf: 'expr(not is_granted("ROLE_SUPER_ADMIN"))')
)]
#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:customer', 'read:product', 'read:user'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['create:customer', 'read:customer', 'read:product', 'read:user'])]
    #[Assert\NotBlank()]
    #[Assert\Length(
        min: 2,
        max: 256,
        minMessage: 'The customer name must be at least {{ limit }} characters long',
        maxMessage: 'The customer name cannot be longer than {{ limit }} characters',
    )]
    private ?string $name = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'customer', cascade: ['remove'])]
    #[Groups(['read:customer'])]
    private Collection $users;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'customers')]
    #[Groups(['read:customer'])]
    private Collection $products;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setCustomer($this);
        }

        return $this;
    }

    /**
     * @param Collection<int, User> $users
     */
    public function setUsers(Collection $users): static {
        $this->users = $users;

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCustomer() === $this) {
                $user->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }

        return $this;
    }

    /**
     * @param Collection<int, Product> $products
     */
    public function setProducts(Collection $products): static {
        $this->products = $products;

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        $this->products->removeElement($product);

        return $this;
    }
}
