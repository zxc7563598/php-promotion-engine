<?php

declare(strict_types=1);

namespace Hejunjie\PromotionEngine\Calculators;

use Hejunjie\PromotionEngine\Contracts\PromotionCalculatorInterface;
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;

/**
 * 独立模式
 * 每条规则基于「商品原价」单独计算优惠金额
 * 最后再统一汇总优惠，总价不会受到其他规则的中间计算结果影响
 */
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
            'details'  => $details,
            'items'    => $cart->getItems()
        ];
    }
}
