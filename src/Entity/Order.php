<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
#[ORM\HasLifecycleCallbacks]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30, unique: true, nullable: true)]
    private ?string $orderNumber = null;

    #[ORM\Column(length: 150)]
    private string $customerName;

    #[ORM\Column(length: 180)]
    private string $customerEmail;

    #[ORM\Column(length: 50)]
    private string $customerPhone;

    #[ORM\Column(length: 150)]
    private string $shippingStreet;

    #[ORM\Column(length: 100)]
    private string $shippingCity;

    #[ORM\Column(length: 100)]
    private string $shippingProvince;

    #[ORM\Column(length: 30)]
    private string $shippingPostcode;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shippingReference = null;

    #[ORM\Column(length: 50)]
    private string $shippingMethodCode;

    #[ORM\Column(length: 150)]
    private string $shippingMethodLabel;

    #[ORM\Column(type: 'integer')]
    private int $shippingCost = 0;

    #[ORM\Column(length: 50)]
    private string $paymentMethodCode;

    #[ORM\Column(length: 150)]
    private string $paymentMethodLabel;

    #[ORM\Column(type: 'integer')]
    private int $subtotal = 0;

    #[ORM\Column(type: 'integer')]
    private int $discountTotal = 0;

    #[ORM\Column(type: 'integer')]
    private int $taxTotal = 0;

    #[ORM\Column(type: 'integer')]
    private int $total = 0;

    #[ORM\Column(length: 30, options: ['default' => 'created'])]
    private string $status = 'created';

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt ??= $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(?string $orderNumber): self
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function getCustomerName(): string
    {
        return $this->customerName;
    }

    public function setCustomerName(string $customerName): self
    {
        $this->customerName = $customerName;
        return $this;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail(string $customerEmail): self
    {
        $this->customerEmail = $customerEmail;
        return $this;
    }

    public function getCustomerPhone(): string
    {
        return $this->customerPhone;
    }

    public function setCustomerPhone(string $customerPhone): self
    {
        $this->customerPhone = $customerPhone;
        return $this;
    }

    public function getShippingStreet(): string
    {
        return $this->shippingStreet;
    }

    public function setShippingStreet(string $shippingStreet): self
    {
        $this->shippingStreet = $shippingStreet;
        return $this;
    }

    public function getShippingCity(): string
    {
        return $this->shippingCity;
    }

    public function setShippingCity(string $shippingCity): self
    {
        $this->shippingCity = $shippingCity;
        return $this;
    }

    public function getShippingProvince(): string
    {
        return $this->shippingProvince;
    }

    public function setShippingProvince(string $shippingProvince): self
    {
        $this->shippingProvince = $shippingProvince;
        return $this;
    }

    public function getShippingPostcode(): string
    {
        return $this->shippingPostcode;
    }

    public function setShippingPostcode(string $shippingPostcode): self
    {
        $this->shippingPostcode = $shippingPostcode;
        return $this;
    }

    public function getShippingReference(): ?string
    {
        return $this->shippingReference;
    }

    public function setShippingReference(?string $shippingReference): self
    {
        $this->shippingReference = $shippingReference;
        return $this;
    }

    public function getShippingMethodCode(): string
    {
        return $this->shippingMethodCode;
    }

    public function setShippingMethodCode(string $shippingMethodCode): self
    {
        $this->shippingMethodCode = $shippingMethodCode;
        return $this;
    }

    public function getShippingMethodLabel(): string
    {
        return $this->shippingMethodLabel;
    }

    public function setShippingMethodLabel(string $shippingMethodLabel): self
    {
        $this->shippingMethodLabel = $shippingMethodLabel;
        return $this;
    }

    public function getShippingCost(): int
    {
        return $this->shippingCost;
    }

    public function setShippingCost(int $shippingCost): self
    {
        $this->shippingCost = $shippingCost;
        return $this;
    }

    public function getPaymentMethodCode(): string
    {
        return $this->paymentMethodCode;
    }

    public function setPaymentMethodCode(string $paymentMethodCode): self
    {
        $this->paymentMethodCode = $paymentMethodCode;
        return $this;
    }

    public function getPaymentMethodLabel(): string
    {
        return $this->paymentMethodLabel;
    }

    public function setPaymentMethodLabel(string $paymentMethodLabel): self
    {
        $this->paymentMethodLabel = $paymentMethodLabel;
        return $this;
    }

    public function getSubtotal(): int
    {
        return $this->subtotal;
    }

    public function setSubtotal(int $subtotal): self
    {
        $this->subtotal = $subtotal;
        return $this;
    }

    public function getDiscountTotal(): int
    {
        return $this->discountTotal;
    }

    public function setDiscountTotal(int $discountTotal): self
    {
        $this->discountTotal = $discountTotal;
        return $this;
    }

    public function getTaxTotal(): int
    {
        return $this->taxTotal;
    }

    public function setTaxTotal(int $taxTotal): self
    {
        $this->taxTotal = $taxTotal;
        return $this;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): self
    {
        $this->total = $total;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
