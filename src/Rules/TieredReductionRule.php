<?php

namespace Hejunjie\PromotionEngine\Rules;

use Hejunjie\PromotionEngine\Contracts\PromotionRuleInterface;
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionResult;

/**
 * 折扣规则：阶梯满 X 元减 Y 元（例：满 100 减 10，满 200 减 30，满 500 减 80（取最高档））
 * @package Hejunjie\PromotionEngine\Rules
 */
class TieredReductionRule implements PromotionRuleInterface
{
    /**
     * 构造函数
     * @param array $tiers 阶梯数组，如 [100 => 10, 200 => 30, 500 => 80]
     * @param array $applicableTags 适用的商品标签，空数组则为不限标签
     * @param int $priority 优先级
     * 
     * @return void
     */
    public function __construct(
        protected array $tiers,
        protected array $applicableTags = [],
        protected int $priority = 1
    ) {
        ksort($this->tiers);
    }

    /**
     * 应用满减规则
     * 
     * @param Cart $cart 购物车对象
     * @param User $user 用户对象
     * @param array $eligibleIndexes 符合条件的商品下标列表
     * 
     * @return PromotionResult 规则应用结果（包含优惠金额 & 描述）
     */
    public function apply(Cart $cart, User $user, array $eligibleIndexes = []): PromotionResult
    {
        $items = array_intersect_key($cart->filterItemsByTags($this->applicableTags), array_flip($eligibleIndexes));
        $eligibleTotal = $cart->calculateItemsTotal($items);
        $bestReduction = 0;
        $bestThreshold = 0;
        // 遍历阶梯，找到「最大满足条件」的档
        foreach ($this->tiers as $threshold => $reduction) {
            if ($eligibleTotal >= $threshold) {
                $bestReduction = $reduction;
                $bestThreshold = $threshold;
            }
        }
        if ($bestReduction > 0) {
            return new PromotionResult(
                $bestReduction,
                "指定商品满{$bestThreshold}减{$bestReduction}"
            );
        }
        return new PromotionResult(0, "未满足阶梯满减条件");
    }

    /**
     * 找出所有符合标签的商品下标
     * 
     * @param Cart $cart 
     * 
     * @return array 
     */
    public function getApplicableItems(Cart $cart): array
    {
        $items = $cart->getItems();
        $indexes = [];
        foreach ($items as $i => $item) {
            if (empty($this->applicableTags) || count(array_intersect($this->applicableTags, $item['tags'])) > 0) {
                $indexes[] = $i;
            }
        }
        return $indexes;
    }

    /**
     * 获取规则优先级
     * 
     * @return int 
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * 获取适用的商品标签
     * 
     * @return array 
     */
    public function getApplicableTags(): array
    {
        return $this->applicableTags;
    }
}
