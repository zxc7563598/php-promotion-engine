<?php

namespace Hejunjie\PromotionEngine\Rules;

use Hejunjie\PromotionEngine\PromotionRule;
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionResult;

// 折扣规则：买满 X 件减 Y 元（例：买 3 件减 20）
class FullQuantityReductionRule implements PromotionRule
{
    /**
     * 构造函数
     * @param int $minItems 满足打折的最少购买数量
     * @param float $reduction 满减金额
     * @param array $applicableTags 适用的商品标签，空数组则为不限标签
     * @param int $priority 优先级
     * 
     * @return void
     */
    public function __construct(
        protected int $minItems,
        protected float $reduction,
        protected array $applicableTags = [],
        protected int $priority = 1
    ) {}

    public function apply(Cart $cart, User $user): PromotionResult
    {
        $items = $cart->filterItemsByTags($this->applicableTags);
        $eligibleTotal = $cart->getItemCount($items);
        if ($eligibleTotal >= $this->minItems) {
            return new PromotionResult($this->reduction, "指定商品满{$this->minItems}件减{$this->reduction}");
        }
        return new PromotionResult(0, "未满足打折条件");
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
