<?php

namespace Hejunjie\PromotionEngine\Calculators;

use Hejunjie\PromotionEngine\Contracts\PromotionCalculatorInterface;
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;

class IndependentCalculator implements PromotionCalculatorInterface
{
    public function calculate(Cart $cart, User $user, array $rules): array
    {
        usort($rules, fn($a, $b) => $a->getPriority() <=> $b->getPriority());
        $totalDiscount = 0;
        $details = [];
        foreach ($rules as $rule) {
            $result = $rule->apply($cart, $user);
            if ($result->hasDiscount()) {
                $totalDiscount += $result->discount;
                $details[] = $result->description . " (-¥{$result->discount})";
            }
        }
        return [
            'original' => $cart->getTotal(),
            'discount' => $totalDiscount,
            'final'    => max(0, $cart->getTotal() - $totalDiscount),
            'details'  => $details
        ];
    }
}
