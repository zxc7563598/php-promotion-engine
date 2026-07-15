<?php

declare(strict_types=1);

namespace Hejunjie\PromotionEngine\Rules;

use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionResult;

/**
 * VIP 立减规则：VIP 用户下单直接减免固定金额
 *
 * VIP 用户下单即减，无需满足额外金额或件数条件。非 VIP 用户无优惠。
 *
 * 示例：VIP 下单减 5 元
 *
 * @package Hejunjie\PromotionEngine\Rules
 */
class VipReductionRule extends AbstractPromotionRule
{
    /**
     * 减免金额（元）
     *
     * @var float
     */
    protected float $reduction;

    /**
     * 构造函数
     *
     * @param float $reduction      立减金额（元）
     * @param array $applicableTags 适用的商品标签，空数组表示不限标签
     * @param int   $priority       优先级（数值越小优先级越高）
     */
    public function __construct(float $reduction, array $applicableTags = [], int $priority = 1)
    {
        $this->reduction = $reduction;
        $this->applicableTags = $applicableTags;
        $this->priority = $priority;
    }

    /**
     * 应用 VIP 立减规则
     *
     * 仅当用户为 VIP 时触发，直接减免固定金额。
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

        return new PromotionResult($this->reduction, "VIP 立减 {$this->reduction} 元");
    }
}
