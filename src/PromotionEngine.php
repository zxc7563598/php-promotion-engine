<?php

namespace Hejunjie\PromotionEngine;

use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;

/**
 * 促销引擎类
 * @package Hejunjie\PromotionEngine
 */
class PromotionEngine
{

    protected array $rules = [];

    public function addRule(PromotionRule $rule): self
    {
        $this->rules[] = $rule;
        return $this;
    }

    public function calculate(Cart $cart, User $user): array
    {
        // 按优先级排序
        usort($this->rules, fn($a, $b) => $a->getPriority() <=> $b->getPriority());
        $totalDiscount = 0;
        $details = [];
        foreach ($this->rules as $rule) {
            $result = $rule->apply($cart, $user);
            if ($result->hasDiscount()) {
                $totalDiscount += $result->discount;
                $details[] = $result->description . " (-¥{$result->discount})";
            }
        }
        // 返回数据
        return [
            'original' => $cart->getTotal(),
            'discount' => $totalDiscount,
            'final'    => max(0, $cart->getTotal() - $totalDiscount),
            'details'  => $details
        ];
    }
}
