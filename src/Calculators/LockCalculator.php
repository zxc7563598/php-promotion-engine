<?php

declare(strict_types=1);

namespace Hejunjie\PromotionEngine\Calculators;

use Hejunjie\PromotionEngine\Contracts\PromotionCalculatorInterface;
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;

/**
 * 锁定模式
 * 每件商品最多只会被一条规则折扣，后续规则不会再对已被锁定的商品重复优惠
 * 适用于“某件商品只能享受一次优惠”的业务场景（如秒杀、专属券等）
 */
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
