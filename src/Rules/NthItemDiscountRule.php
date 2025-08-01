<?php

namespace Hejunjie\PromotionEngine\Rules;

use Hejunjie\PromotionEngine\Contracts\PromotionRuleInterface;
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionResult;

/**
 * 折扣规则：第 N 件打 Z 折（例：第 3 件 5 折）
 * @package Hejunjie\PromotionEngine\Rules
 */
class NthItemDiscountRule implements PromotionRuleInterface
{
    /**
     * 构造函数
     * @param int $nthItem 第几件
     * @param float $discountRate 折扣率（0.8 = 8折）
     * @param array $applicableTags 适用的商品标签，空数组则为不限标签
     * @param int $priority 优先级
     * 
     * @return void
     */
    public function __construct(
        protected int $nthItem,
        protected float $discountRate,
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
        // 过滤适用的商品
        $items = array_intersect_key($cart->filterItemsByTags($this->applicableTags), array_flip($eligibleIndexes));
        if (count($items) >= $this->nthItem) {
            $nthItem = $items[$this->nthItem - 1];
            $discountAmount = $nthItem['price'] * (1 - $this->discountRate);
            return new PromotionResult($discountAmount, "指定商品第{$this->nthItem}件打" . ($this->discountRate * 10) . "折");
        }
        return new PromotionResult(0, "未满足第{$this->nthItem}件打折条件");
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
