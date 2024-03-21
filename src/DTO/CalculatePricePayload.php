<?php declare(strict_types=1);

namespace App\DTO;

use App\Validator\CouponExist;
use App\Validator\ProductExist;
use App\Validator\TaxNumberInvalid;
use Symfony\Component\Validator\Constraints as Assert;

class CalculatePricePayload
{
    #[Assert\NotBlank]
    #[ProductExist]
    public int $product;

    #[Assert\NotBlank]
    #[TaxNumberInvalid]
    public string $taxNumber = '';

    #[CouponExist]
    public string $couponCode = '';
}
