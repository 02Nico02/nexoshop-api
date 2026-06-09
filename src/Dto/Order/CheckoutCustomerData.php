<?php

namespace App\Dto\Order;

use Symfony\Component\Validator\Constraints as Assert;

class CheckoutCustomerData
{
    #[Assert\NotBlank(message: 'El nombre es requerido')]
    public string $name = '';

    #[Assert\NotBlank(message: 'El email es requerido')]
    #[Assert\Email(message: 'El email no es válido')]
    public string $email = '';

    #[Assert\NotBlank(message: 'El teléfono es requerido')]
    public string $phone = '';
}
