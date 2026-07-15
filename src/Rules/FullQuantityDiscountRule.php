<?php

declare(strict_types=1);

namespace Hejunjie\PromotionEngine\Rules;

use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionResult;

/**
 * 件数折扣规则：买满 X 件打 Z 折
 *
 * 当购物车中符合条件的商品总数量达到指定件数时，对商品总额打折。
 *
 * 示例：买 3 件打 9 折（discountRate = 0.9）
 *
 * @package Hejunjie\PromotionEngine\Rules
 */
class FullQuantityDiscountRule extends AbstractPromotionRule
{
    /**
     * 最低购买件数
     *
     * @var int
     */
    protected int $minItems;

    /**
     * 折扣率（0.8 表示 8 折）
     *
     * @var float
     */
    protected float $discountRate;

    /**
     * 构造函数
     *
     * @param int   $minItems       满足打折的最少购买数量
     * @param float $discountRate   折扣率（0.8 = 8折）
     * @param array $applicableTags 适用的商品标签，空数组表示不限标签
     * @param int   $priority       优先级（数值越小优先级越高）
     */
    public function __construct(int $minItems, float $discountRate, array $applicableTags = [], int $priority = 1)
    {
        $this->minItems = $minItems;
        $this->discountRate = $discountRate;
        $this->applicableTags = $applicableTags;
        $this->priority = $priority;
    }

    /**
     * 应用件数折扣规则
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
        $eligibleCount = $cart->getItemCount($items);

        if ($eligibleCount >= $this->minItems) {
            $discount = $cart->calculateItemsTotal($items) * (1 - $this->discountRate);
            return new PromotionResult($discount, "指定商品满{$this->minItems}件打" . ($this->discountRate * 10) . "折");
        }

        return new PromotionResult(0, "未满足件数折扣条件");
    }
}
