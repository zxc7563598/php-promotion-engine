<?php

namespace Hejunjie\PromotionEngine\Calculators;

use Hejunjie\PromotionEngine\Contracts\PromotionCalculatorInterface;
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;

class LockCalculator implements PromotionCalculatorInterface
{
    public function calculate(Cart $cart, User $user, array $rules): array
    {
        usort($rules, fn($a, $b) => $a->getPriority() <=> $b->getPriority());
        $totalDiscount = 0;
        $details = [];
        foreach ($rules as $rule) {
            $eligibleIndexes = $rule->getApplicableItems($cart);
            $eligibleIndexes = array_filter($eligibleIndexes, fn($i) => !$cart->isLocked($i));
            if (empty($eligibleIndexes)) {
                continue;
            }
            $result = $rule->apply($cart, $user, $eligibleIndexes);
            if ($result->hasDiscount()) {
                $totalDiscount += $result->discount;
                $details[] = $result->description . " (-¥{$result->discount})";
                $cart->lockItems($eligibleIndexes);
            }
        }
        return [
            'original' => $cart->getOriginalTotal(),
            'discount' => $totalDiscount,
            'final'    => max(0, $cart->getTotal()-$totalDiscount),
            'details'  => $details,
            'items'    => $cart->getItems()
        ];
    }
}
