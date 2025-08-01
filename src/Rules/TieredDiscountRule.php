<?php

namespace Hejunjie\PromotionEngine\Rules;

use Hejunjie\PromotionEngine\Contracts\PromotionRuleInterface;
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionResult;

/**
 * 折扣规则：阶梯满 X 元打 Z 折（例：满 100 打 9 折，满 200 打 8 折，满 500 打 7 折（取最高档））
 * @package Hejunjie\PromotionEngine\Rules
 */
class TieredDiscountRule implements PromotionRuleInterface
{
    /**
     * 构造函数
     * @param array $tiers 阶梯数组，如 [100 => 0.9, 200 => 0.8, 500 => 0.7]
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
        $bestDiscountRate = 1;
        $bestThreshold = 0;
        // 遍历阶梯，找到「最大满足条件」的档
        foreach ($this->tiers as $threshold => $discountRate) {
            if ($eligibleTotal >= $threshold) {
                $bestDiscountRate = $discountRate;
                $bestThreshold = $threshold;
            }
        }
        if ($bestDiscountRate < 1) {
            return new PromotionResult($eligibleTotal * (1 - $bestDiscountRate), "指定商品满{$bestThreshold}元打" . ($bestDiscountRate * 10) . "折");
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
