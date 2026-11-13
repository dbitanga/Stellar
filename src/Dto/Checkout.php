<?php

namespace App\Dto;

use App\Entity\Order;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/** Delivery details and payment method entered in the checkout form. Mutable because the Form component writes to it. */
class Checkout
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public ?string $shippingName = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    #[Assert\Regex(pattern: '/^\+?[\d\s-]{7,20}$/', message: 'Enter a valid mobile number.')]
    public ?string $shippingMobile = null;

    #[Assert\NotBlank]
    public ?string $shippingAddress = null;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: Order::PAYMENT_METHODS)]
    public ?string $paymentMethod = Order::PAYMENT_COD;

    /** Pre-fills the form from the customer's profile, like the midterm checkout did. */
    public static function forCustomer(User $customer): self
    {
        $checkout = new self();
        $checkout->shippingName = $customer->getFullName() ?: $customer->getUsername();
        $checkout->shippingMobile = $customer->getMobileNumber();
        $checkout->shippingAddress = $customer->getDeliveryAddress();

        return $checkout;
    }
}
