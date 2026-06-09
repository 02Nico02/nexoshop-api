<?php

namespace App\Dto\Order;

use Symfony\Component\Validator\Constraints as Assert;

class CheckoutShippingAddressData
{
    #[Assert\NotBlank(message: 'La calle es requerida')]
    public string $street = '';

    #[Assert\NotBlank(message: 'La ciudad es requerida')]
    public string $city = '';

    #[Assert\NotBlank(message: 'La provincia es requerida')]
    public string $province = '';

    #[Assert\NotBlank(message: 'El código postal es requerido')]
    public string $postcode = '';

    public ?string $reference = null;
}
