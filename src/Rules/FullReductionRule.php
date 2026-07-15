<?php

declare(strict_types=1);

namespace Hejunjie\PromotionEngine\Rules;

use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionResult;

/**
 * 满减规则：满 X 元减 Y 元
 *
 * 当购物车中符合条件的商品总价达到指定阈值时，直接减免固定金额。
 *
 * 示例：满 100 减 20
 *
 * @package Hejunjie\PromotionEngine\Rules
 */
class FullReductionRule extends AbstractPromotionRule
{
    /**
     * 满减金额阈值（元）
     *
     * @var float
     */
    protected float $threshold;

    /**
     * 减免金额（元）
     *
     * @var float
     */
    protected float $reduction;

    /**
     * 构造函数
     *
     * @param float $threshold      满足条件的金额阈值（元）
     * @param float $reduction      满减金额（元）
     * @param array $applicableTags 适用的商品标签，空数组表示不限标签
     * @param int   $priority       优先级（数值越小优先级越高）
     */
    public function __construct(float $threshold, float $reduction, array $applicableTags = [], int $priority = 1)
    {
        $this->threshold = $threshold;
        $this->reduction = $reduction;
        $this->applicableTags = $applicableTags;
        $this->priority = $priority;
    }

    /**
     * 应用满减规则
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
            return new PromotionResult($this->reduction, "指定商品满{$this->threshold}减{$this->reduction}");
        }

        return new PromotionResult(0, "未满足满减条件");
    }
}
