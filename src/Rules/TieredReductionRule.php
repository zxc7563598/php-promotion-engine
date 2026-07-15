<?php

declare(strict_types=1);

namespace Hejunjie\PromotionEngine\Rules;

use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionResult;

/**
 * 阶梯满减规则：满 X 元减 Y 元（取最高档）
 *
 * 按照阶梯配置，金额每越过一档阈值就减免对应金额，自动取最高档。
 *
 * 示例：满 100 减 10，满 200 减 30，满 500 减 80
 *
 * @package Hejunjie\PromotionEngine\Rules
 */
class TieredReductionRule extends AbstractPromotionRule
{
    /**
     * 阶梯配置（金额阈值 => 减免金额）
     *
     * 例：[100 => 10, 200 => 30, 500 => 80]
     *
     * @var array
     */
    protected array $tiers;

    /**
     * 构造函数
     *
     * @param array $tiers          阶梯数组，格式 [金额阈值 => 减免金额]
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
     * 应用阶梯满减规则
     *
     * 遍历阶梯档位，找到金额满足的最高档减免。
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

        $bestReduction = 0;
        $bestThreshold = 0;

        foreach ($this->tiers as $threshold => $reduction) {
            if ($eligibleTotal >= $threshold) {
                $bestReduction = $reduction;
                $bestThreshold = $threshold;
            }
        }

        if ($bestReduction > 0) {
            return new PromotionResult($bestReduction, "指定商品满{$bestThreshold}减{$bestReduction}");
        }

        return new PromotionResult(0, "未满足阶梯满减条件");
    }
}
