<?php

namespace App\Dto\Order;

use Symfony\Component\Validator\Constraints as Assert;

class CreateOrderRequest
{
    #[Assert\Valid]
    public CheckoutCustomerData $customer;

    #[Assert\Valid]
    public CheckoutShippingAddressData $shippingAddress;

    #[Assert\NotBlank(message: 'El método de envío es requerido')]
    public string $shippingMethod = '';

    #[Assert\NotBlank(message: 'El método de pago es requerido')]
    public string $paymentMethod = '';

    public ?array $paymentDetails = null;

    /**
     * @var CheckoutItemData[]
     */
    #[Assert\Valid]
    #[Assert\Count(min: 1, minMessage: 'Debes enviar al menos un item')]
    public array $items = [];

    public function __construct()
    {
        $this->customer = new CheckoutCustomerData();
        $this->shippingAddress = new CheckoutShippingAddressData();
    }
}
