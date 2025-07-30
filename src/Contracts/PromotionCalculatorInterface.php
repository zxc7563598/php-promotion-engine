<?php

namespace Hejunjie\PromotionEngine\Contracts;

use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;

interface PromotionCalculatorInterface
{
    /**
     * 计算优惠
     * 
     * @param Cart $cart
     * @param User $user
     * @param PromotionRuleInterface[] $rules
     * 
     * @return array
     */
    public function calculate(Cart $cart, User $user, array $rules): array;
}
