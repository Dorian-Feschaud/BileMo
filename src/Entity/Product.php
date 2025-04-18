<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[Hateoas\Relation(
    'self',
    href: new Hateoas\Route(name: 'product', parameters: ['id' => 'expr(object.getId())']),
    attributes: ["method" => "GET"],
    exclusion: new Hateoas\Exclusion(groups: ['read:product'])
)]
#[Hateoas\Relation(
    'update',
    href: new Hateoas\Route(name: 'updateProduct', parameters: ['id' => 'expr(object.getId())']),
    attributes: ["method" => "PUT"],
    exclusion: new Hateoas\Exclusion(groups: ['read:product'], excludeIf: 'expr(not is_granted("ROLE_SUPER_ADMIN"))')
)]
#[Hateoas\Relation(
    'delete',
    href: new Hateoas\Route(name: 'deleteProduct', parameters: ['id' => 'expr(object.getId())']),
    attributes: ["method" => "DELETE"],
    exclusion: new Hateoas\Exclusion(groups: ['read:product'], excludeIf: 'expr(not is_granted("ROLE_SUPER_ADMIN"))')
)]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:product', 'read:customer'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['create:product', 'read:product', 'read:customer'])]
    #[Assert\NotBlank()]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['create:product', 'read:product'])]
    #[Assert\NotBlank()]
    private ?string $manufacturer = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['create:product', 'read:product'])]
    #[Assert\NotNull()]
    private ?\DateTimeInterface $releaseDate = null;

    #[ORM\Column]
    #[Groups(['create:product', 'read:product'])]
    #[Assert\NotNull()]
    private ?float $price = null;

    #[ORM\Column(length: 255)]
    #[Groups(['create:product', 'read:product'])]
    #[Assert\NotBlank()]
    private ?string $color = null;

    #[ORM\Column]
    #[Groups(['create:product', 'read:product'])]
    #[Assert\NotNull()]
    private ?int $capacity = null;

    #[ORM\Column]
    #[Groups(['create:product', 'read:product'])]
    private ?float $height = null;

    #[ORM\Column]
    #[Groups(['create:product', 'read:product'])]
    #[Assert\NotNull()]
    private ?float $width = null;

    #[ORM\Column]
    #[Groups(['create:product', 'read:product'])]
    #[Assert\NotNull()]
    private ?float $thickness = null;

    #[ORM\Column]
    #[Groups(['create:product', 'read:product'])]
    #[Assert\NotNull()]
    private ?int $weight = null;

    #[ORM\Column(length: 255)]
    #[Groups(['create:product', 'read:product'])]
    #[Assert\NotBlank()]
    private ?string $screen = null;

    #[ORM\Column]
    #[Groups(['create:product', 'read:product'])]
    #[Assert\NotNull()]
    private ?float $screenHeight = null;

    #[ORM\Column]
    #[Groups(['create:product', 'read:product'])]
    #[Assert\NotNull()]
    private ?float $screenWidth = null;

    #[ORM\Column(length: 255)]
    #[Groups(['create:product', 'read:product'])]
    #[Assert\NotNull()]
    private ?int $screenResolution = null;

    #[ORM\Column(length: 255)]
    #[Groups(['create:product', 'read:product'])]
    #[Assert\NotBlank()]
    private ?string $backCamera = null;

    #[ORM\Column(length: 255)]
    #[Groups(['create:product', 'read:product'])]
    #[Assert\NotNull()]
    private ?int $backCameraResolution = null;

    #[ORM\Column(length: 255)]
    #[Groups(['create:product', 'read:product'])]
    #[Assert\NotNull()]
    private ?int $frontCameraResolution = null;

    #[ORM\Column(length: 255)]
    #[Groups(['create:product', 'read:product'])]
    #[Assert\NotBlank()]
    private ?string $processor = null;

    #[ORM\Column]
    #[Groups(['create:product', 'read:product'])]
    #[Assert\NotNull()]
    private ?int $ram = null;

    #[ORM\Column]
    #[Groups(['create:product', 'read:product'])]
    #[Assert\NotNull()]
    private ?int $batteryCapacity = null;

    #[ORM\Column(length: 255)]
    #[Groups(['create:product', 'read:product'])]
    #[Assert\NotBlank()]
    private ?string $network = null;

    /**
     * @var Collection<int, Customer>
     */
    #[ORM\ManyToMany(targetEntity: Customer::class, mappedBy: 'products')]
    #[Groups(['read:product:superadmin'])]
    private Collection $customers;

    public function __construct()
    {
        $this->customers = new ArrayCollection();
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

    public function getManufacturer(): ?string
    {
        return $this->manufacturer;
    }

    public function setManufacturer(string $manufacturer): static
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(\DateTimeInterface $releaseDate): static
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setHeight(float $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getWidth(): ?float
    {
        return $this->width;
    }

    public function setWidth(float $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getThickness(): ?float
    {
        return $this->thickness;
    }

    public function setThickness(float $thickness): static
    {
        $this->thickness = $thickness;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getScreen(): ?string
    {
        return $this->screen;
    }

    public function setScreen(string $screen): static
    {
        $this->screen = $screen;

        return $this;
    }

    public function getScreenHeight(): ?float
    {
        return $this->screenHeight;
    }

    public function setScreenHeight(float $screenHeight): static
    {
        $this->screenHeight = $screenHeight;

        return $this;
    }

    public function getScreenWidth(): ?float
    {
        return $this->screenWidth;
    }

    public function setScreenWidth(float $screenWidth): static
    {
        $this->screenWidth = $screenWidth;

        return $this;
    }

    public function getScreenResolution(): ?int
    {
        return $this->screenResolution;
    }

    public function setScreenResolution(int $screenResolution): static
    {
        $this->screenResolution = $screenResolution;

        return $this;
    }

    public function getBackCamera(): ?string
    {
        return $this->backCamera;
    }

    public function setBackCamera(string $backCamera): static
    {
        $this->backCamera = $backCamera;

        return $this;
    }

    public function getBackCameraResolution(): ?int
    {
        return $this->backCameraResolution;
    }

    public function setBackCameraResolution(int $backCameraResolution): static
    {
        $this->backCameraResolution = $backCameraResolution;

        return $this;
    }

    public function getFrontCameraResolution(): ?int
    {
        return $this->frontCameraResolution;
    }

    public function setFrontCameraResolution(int $frontCameraResolution): static
    {
        $this->frontCameraResolution = $frontCameraResolution;

        return $this;
    }

    public function getProcessor(): ?string
    {
        return $this->processor;
    }

    public function setProcessor(string $processor): static
    {
        $this->processor = $processor;

        return $this;
    }

    public function getRam(): ?int
    {
        return $this->ram;
    }

    public function setRam(int $ram): static
    {
        $this->ram = $ram;

        return $this;
    }

    public function getBatteryCapacity(): ?int
    {
        return $this->batteryCapacity;
    }

    public function setBatteryCapacity(int $batteryCapacity): static
    {
        $this->batteryCapacity = $batteryCapacity;

        return $this;
    }

    public function getNetwork(): ?string
    {
        return $this->network;
    }

    public function setNetwork(string $network): static
    {
        $this->network = $network;

        return $this;
    }

    /**
     * @return Collection<int, Customer>
     */
    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function addCustomer(Customer $customer): static
    {
        if (!$this->customers->contains($customer)) {
            $this->customers->add($customer);
            $customer->addProduct($this);
        }

        return $this;
    }

    public function removeCustomer(Customer $customer): static
    {
        if ($this->customers->removeElement($customer)) {
            $customer->removeProduct($this);
        }

        return $this;
    }
}
