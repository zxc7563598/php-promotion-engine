<?php

declare(strict_types=1);

namespace Hejunjie\PromotionEngine\Rules;

use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionResult;

/**
 * VIP 折扣规则：VIP 用户享受指定折扣
 *
 * VIP 用户对符合条件的商品享受折扣，非 VIP 用户无优惠。
 *
 * 示例：VIP 9 折（discountRate = 0.9）
 *
 * @package Hejunjie\PromotionEngine\Rules
 */
class VipDiscountRule extends AbstractPromotionRule
{
    /**
     * 折扣率（0.8 表示 8 折）
     *
     * @var float
     */
    protected float $discountRate;

    /**
     * 构造函数
     *
     * @param float $discountRate   折扣率（0.8 = 8折）
     * @param array $applicableTags 适用的商品标签，空数组表示不限标签
     * @param int   $priority       优先级（数值越小优先级越高）
     */
    public function __construct(float $discountRate, array $applicableTags = [], int $priority = 1)
    {
        $this->discountRate = $discountRate;
        $this->applicableTags = $applicableTags;
        $this->priority = $priority;
    }

    /**
     * 应用 VIP 折扣规则
     *
     * 仅当用户为 VIP 时触发，对符合条件的商品总额打折。
     *
     * @param Cart  $cart             购物车对象
     * @param User  $user             用户对象
     * @param array $eligibleIndexes  符合条件的商品下标列表（锁模式使用）
     *
     * @return PromotionResult 规则应用结果
     */
    public function apply(Cart $cart, User $user, array $eligibleIndexes = []): PromotionResult
    {
        if (!$user->isVip()) {
            return new PromotionResult(0, "非VIP用户");
        }

        $items = $this->filterEligibleItems($cart, $eligibleIndexes);
        $eligibleTotal = $cart->calculateItemsTotal($items);
        $discount = $eligibleTotal * (1 - $this->discountRate);

        return new PromotionResult($discount, "VIP " . ($this->discountRate * 10) . " 折");
    }
}
