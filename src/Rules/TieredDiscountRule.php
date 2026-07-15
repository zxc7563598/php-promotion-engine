<?php

declare(strict_types=1);

namespace Hejunjie\PromotionEngine\Rules;

use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionResult;

/**
 * 阶梯折扣规则：满 X 元打 Z 折（取最高档）
 *
 * 按照阶梯配置，金额每越过一档阈值就打对应的折扣，自动取最高档。
 *
 * 示例：满 100 打 9 折，满 200 打 8 折，满 500 打 7 折
 *
 * @package Hejunjie\PromotionEngine\Rules
 */
class TieredDiscountRule extends AbstractPromotionRule
{
    /**
     * 阶梯配置（金额阈值 => 折扣率）
     *
     * 例：[100 => 0.9, 200 => 0.8, 500 => 0.7]
     *
     * @var array
     */
    protected array $tiers;

    /**
     * 构造函数
     *
     * @param array $tiers          阶梯数组，格式 [金额阈值 => 折扣率]
     * @param array $applicableTags 适用的商品标签，空数组表示不限标签
     * @param int   $priority       优先级（数值越小优先级越高）
     */
    public function __construct(array $tiers, array $applicableTags = [], int $priority = 1)
    {
        $this->tiers = $tiers;
        $this->applicableTags = $applicableTags;
        $this->priority = $priority;
        ksort($this->tiers);
    }

    /**
     * 应用阶梯折扣规则
     *
     * 遍历阶梯档位，找到金额满足的最高档应用折扣。
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

        $bestDiscountRate = 1;
        $bestThreshold = 0;

        foreach ($this->tiers as $threshold => $discountRate) {
            if ($eligibleTotal >= $threshold) {
                $bestDiscountRate = $discountRate;
                $bestThreshold = $threshold;
            }
        }

        if ($bestDiscountRate < 1) {
            $discount = $eligibleTotal * (1 - $bestDiscountRate);
            return new PromotionResult($discount, "指定商品满{$bestThreshold}元打" . ($bestDiscountRate * 10) . "折");
        }

        return new PromotionResult(0, "未满足阶梯折扣条件");
    }
}
