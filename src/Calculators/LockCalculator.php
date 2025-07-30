<?php

namespace Hejunjie\PromotionEngine\Calculators;

use Hejunjie\PromotionEngine\Contracts\PromotionCalculatorInterface;
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;

class LockCalculator implements PromotionCalculatorInterface
{
    public function calculate(Cart $cart, User $user, array $rules): array
    {
        // TODO: 先空着，后续实现锁定逻辑
        return (new IndependentCalculator())->calculate($cart, $user, $rules);
    }
}
