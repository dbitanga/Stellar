<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
class Order
{
    public const PAYMENT_COD = 'COD';
    public const PAYMENT_CARD = 'Card';
    public const PAYMENT_METHODS = [self::PAYMENT_COD, self::PAYMENT_CARD];

    public const PAYMENT_PENDING = 'Pending';
    public const PAYMENT_PAID = 'Paid';
    public const PAYMENT_REFUNDED = 'Refunded';
    public const PAYMENT_STATUSES = [self::PAYMENT_PENDING, self::PAYMENT_PAID, self::PAYMENT_REFUNDED];

    public const STATUS_PROCESSING = 'Processing';
    public const STATUS_SHIPPED = 'Shipped';
    public const STATUS_DELIVERED = 'Delivered';
    public const STATUS_CANCELLED = 'Cancelled';
    public const STATUS_RETURN_REQUESTED = 'Return requested';
    public const STATUS_RETURNED = 'Returned';
    public const ORDER_STATUSES = [self::STATUS_PROCESSING, self::STATUS_SHIPPED, self::STATUS_DELIVERED, self::STATUS_CANCELLED, self::STATUS_RETURN_REQUESTED, self::STATUS_RETURNED];

    public const RETURN_REASONS = ['Defective', 'Wrong item', 'Changed mind'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'order_id')]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\PositiveOrZero]
    private ?string $totalAmount = null;

    #[ORM\Column(length: 10)]
    #[Assert\Choice(choices: self::PAYMENT_METHODS)]
    private ?string $paymentMethod = null;

    #[ORM\Column(length: 10, options: ['default' => 'Pending'])]
    #[Assert\Choice(choices: self::PAYMENT_STATUSES)]
    private string $paymentStatus = 'Pending';

    #[ORM\Column(length: 20, options: ['default' => 'Processing'])]
    #[Assert\Choice(choices: self::ORDER_STATUSES)]
    private string $orderStatus = 'Processing';

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $shippingName = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    private ?string $shippingMobile = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $shippingAddress = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /** Set when the (simulated) customer will send the order back once it arrives. */
    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Choice(choices: self::RETURN_REASONS)]
    private ?string $returnReason = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $returnRequestedAt = null;

    /** @var Collection<int, OrderItem> */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'customerOrder', cascade: ['persist'])]
    private Collection $items;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(?string $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getPaymentStatus(): string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(string $paymentStatus): static
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    public function getOrderStatus(): string
    {
        return $this->orderStatus;
    }

    public function setOrderStatus(string $orderStatus): static
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }

    public function getShippingName(): ?string
    {
        return $this->shippingName;
    }

    public function setShippingName(?string $shippingName): static
    {
        $this->shippingName = $shippingName;

        return $this;
    }

    public function getShippingMobile(): ?string
    {
        return $this->shippingMobile;
    }

    public function setShippingMobile(?string $shippingMobile): static
    {
        $this->shippingMobile = $shippingMobile;

        return $this;
    }

    public function getShippingAddress(): ?string
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(?string $shippingAddress): static
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getReturnReason(): ?string
    {
        return $this->returnReason;
    }

    public function setReturnReason(?string $returnReason): static
    {
        $this->returnReason = $returnReason;

        return $this;
    }

    public function getReturnRequestedAt(): ?\DateTimeImmutable
    {
        return $this->returnRequestedAt;
    }

    public function setReturnRequestedAt(?\DateTimeImmutable $returnRequestedAt): static
    {
        $this->returnRequestedAt = $returnRequestedAt;

        return $this;
    }

    /** @return Collection<int, OrderItem> */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function getItemCount(): int
    {
        $count = 0;
        foreach ($this->items as $item) {
            $count += (int) $item->getQuantity();
        }

        return $count;
    }

    public function addItem(OrderItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setCustomerOrder($this);
        }

        return $this;
    }
}