<?php

declare(strict_types=1);

namespace Hejunjie\PromotionEngine\Calculators;

use Hejunjie\PromotionEngine\Contracts\PromotionCalculatorInterface;
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;

/**
 * 折上折模式
 * 每条规则基于「上一次计算后的价格」继续计算，形成“折上折”效果
 * 当一条规则涉及多个商品时，会按商品原价在组合总价中的比例分摊优惠金额
 * 以确保每件商品的价格在后续计算中被正确更新
 */
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
                $discount = round($result->discount, 2);
                $totalDiscount += $discount;
                $details[] = $result->description . " (-¥{$discount})";
                if (method_exists($rule, 'getApplicableItems')) {
                    $indexes = $rule->getApplicableItems($cart);
                    $cart->applyDiscountToItems($indexes, $discount);
                }
                $totalDiscount = round($totalDiscount, 2); // 确保总折扣是两位小数
            }
        }
        return [
            'original' => $cart->getOriginalTotal(),
            'discount' => $totalDiscount,
            'final'    => $cart->getOriginalTotal() - $totalDiscount,
            'details'  => $details,
            'items'    => $cart->getItems()
        ];
    }
}
