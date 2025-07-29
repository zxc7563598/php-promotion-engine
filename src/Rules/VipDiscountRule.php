<?php

namespace Hejunjie\PromotionEngine\Rules;

use Hejunjie\PromotionEngine\PromotionRule;
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionResult;

/**
 * 折扣规则：VIP 用户享受折扣（例：VIP 9 折）
 * @package Hejunjie\PromotionEngine\Rules
 */
class VipDiscountRule implements PromotionRule
{
    /**
     * 构造函数
     * @param float $discountRate 折扣率（0.8 = 8折）
     * @param array $applicableTags 适用的商品标签，空数组则为不限标签
     * @param int $priority 优先级
     * 
     * @return void 
     */
    public function __construct(
        protected float $discountRate,
        protected array $applicableTags = [],
        protected int $priority = 1
    ) {}

    /**
     * 应用满减规则
     * 
     * @param Cart $cart 购物车对象
     * @param User $user 用户对象
     * 
     * @return PromotionResult 规则应用结果（包含优惠金额 & 描述）
     */
    public function apply(Cart $cart, User $user): PromotionResult
    {
        if (!$user->isVip()){
            return new PromotionResult(0, "非VIP用户");
        }
        $items = $cart->filterItemsByTags($this->applicableTags);
        $eligibleTotal = $cart->calculateItemsTotal($items);
        $discount = $eligibleTotal * (1 - $this->discountRate);
        return new PromotionResult($discount, "VIP {$this->discountRate} 折");
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
