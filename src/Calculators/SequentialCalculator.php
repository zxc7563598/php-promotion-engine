<?php

namespace Hejunjie\PromotionEngine\Calculators;

use Hejunjie\PromotionEngine\Contracts\PromotionCalculatorInterface;
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;

class SequentialCalculator implements PromotionCalculatorInterface
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
                if (method_exists($rule, 'getApplicableItems')) {
                    $indexes = $rule->getApplicableItems($cart);
                    $cart->applyDiscountToItems($indexes, $result->discount);
                }
                $totalDiscount = round($totalDiscount, 2); // 确保总折扣是两位小数
            }
        }
        return [
            'original' => $cart->getOriginalTotal(),
            'discount' => $totalDiscount,
            'final'    => $cart->getOriginalTotal() - $totalDiscount,
            'details'  => $details
        ];
    }
}
