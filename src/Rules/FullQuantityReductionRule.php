<?php

declare(strict_types=1);

namespace Hejunjie\PromotionEngine\Rules;

use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionResult;

/**
 * 件数满减规则：买满 X 件减 Y 元
 *
 * 当购物车中符合条件的商品总数量达到指定件数时，直接减免固定金额。
 *
 * 示例：买 3 件减 20
 *
 * @package Hejunjie\PromotionEngine\Rules
 */
class FullQuantityReductionRule extends AbstractPromotionRule
{
    /**
     * 最低购买件数
     *
     * @var int
     */
    protected int $minItems;

    /**
     * 减免金额（元）
     *
     * @var float
     */
    protected float $reduction;

    /**
     * 构造函数
     *
     * @param int   $minItems       满足打折的最少购买数量
     * @param float $reduction      满减金额（元）
     * @param array $applicableTags 适用的商品标签，空数组表示不限标签
     * @param int   $priority       优先级（数值越小优先级越高）
     */
    public function __construct(int $minItems, float $reduction, array $applicableTags = [], int $priority = 1)
    {
        $this->minItems = $minItems;
        $this->reduction = $reduction;
        $this->applicableTags = $applicableTags;
        $this->priority = $priority;
    }

    /**
     * 应用件数满减规则
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
            return new PromotionResult($this->reduction, "指定商品满{$this->minItems}件减{$this->reduction}");
        }

        return new PromotionResult(0, "未满足件数满减条件");
    }
}
