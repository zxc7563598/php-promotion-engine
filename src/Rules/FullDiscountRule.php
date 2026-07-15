<?php

declare(strict_types=1);

namespace Hejunjie\PromotionEngine\Rules;

use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionResult;

/**
 * 满折规则：满 X 元打 Z 折
 *
 * 当购物车中符合条件的商品总价达到指定阈值时，对总额打折。
 *
 * 示例：满 200 打 8 折（discountRate = 0.8）
 *
 * @package Hejunjie\PromotionEngine\Rules
 */
class FullDiscountRule extends AbstractPromotionRule
{
    /**
     * 满折金额阈值（元）
     *
     * @var float
     */
    protected float $threshold;

    /**
     * 折扣率（0.8 表示 8 折）
     *
     * @var float
     */
    protected float $discountRate;

    /**
     * 构造函数
     *
     * @param float $threshold      满足条件的金额阈值（元）
     * @param float $discountRate   折扣率（0.8 = 8折）
     * @param array $applicableTags 适用的商品标签，空数组表示不限标签
     * @param int   $priority       优先级（数值越小优先级越高）
     */
    public function __construct(float $threshold, float $discountRate, array $applicableTags = [], int $priority = 1)
    {
        $this->threshold = $threshold;
        $this->discountRate = $discountRate;
        $this->applicableTags = $applicableTags;
        $this->priority = $priority;
    }

    /**
     * 应用满折规则
     *
     * @param Cart  $cart             购物车对象
     * @param User  $user             用户对象
     * @param array $eligibleIndexes  符合条件的商品下标列表（锁模式使用）
     *
     * @return PromotionResult 规则应用结果
     */
    public function apply(Cart $cart, User $user, array $eligibleIndexes = []): PromotionResult
    {
        $items = $this->filterEligibleItems($cart, $eligibleIndexes);
        $eligibleTotal = $cart->calculateItemsTotal($items);

        if ($eligibleTotal >= $this->threshold) {
            $discount = $eligibleTotal * (1 - $this->discountRate);
            return new PromotionResult($discount, "指定商品满{$this->threshold}元打" . ($this->discountRate * 10) . "折");
        }

        return new PromotionResult(0, "未满足满折条件");
    }
}
