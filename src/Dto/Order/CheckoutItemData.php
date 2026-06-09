<?php

namespace App\Dto\Order;

use Symfony\Component\Validator\Constraints as Assert;

class CheckoutItemData
{
    #[Assert\Positive(message: 'El productId debe ser mayor a 0')]
    public int $productId = 0;

    public ?int $variantId = null;

    #[Assert\Positive(message: 'La cantidad debe ser mayor a 0')]
    public int $quantity = 0;
}
