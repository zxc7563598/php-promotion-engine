<?php

namespace Hejunjie\PromotionEngine\Calculators;

use Hejunjie\PromotionEngine\Contracts\PromotionCalculatorInterface;
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;

class SequentialCalculator implements PromotionCalculatorInterface
{
    public function calculate(Cart $cart, User $user, array $rules): array
    {
        // TODO: 先空着，后续实现折上折逻辑
        return (new IndependentCalculator())->calculate($cart, $user, $rules);
    }
}
