<?php

namespace Hejunjie\PromotionEngine\Rules;

use Hejunjie\PromotionEngine\Contracts\PromotionRuleInterface;
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionResult;

/**
 * 折扣规则：满 X 元减 Y 元（例：满 100 减 20）
 * @package Hejunjie\PromotionEngine\Rules
 */
class FullReductionRule implements PromotionRuleInterface
{
    /**
     * 构造函数
     * @param float $threshold 满足满减的金额阈值
     * @param float $reduction 满减金额
     * @param array $applicableTags 适用的商品标签，空数组则为不限标签
     * @param int $priority 优先级
     * 
     * @return void
     */
    public function __construct(
        protected float $threshold,
        protected float $reduction,
        protected array $applicableTags = [],
        protected int $priority = 1
    ) {}

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
        $items = array_flip($eligibleIndexes) ? array_intersect_key($cart->filterItemsByTags($this->applicableTags), array_flip($eligibleIndexes)) : $cart->filterItemsByTags($this->applicableTags);
        $eligibleTotal = $cart->calculateItemsTotal($items);
        if ($eligibleTotal >= $this->threshold) {
            return new PromotionResult($this->reduction, "指定商品满{$this->threshold}减{$this->reduction}");
        }
        return new PromotionResult(0, "未满足满减条件");
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
