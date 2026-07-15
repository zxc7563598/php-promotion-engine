<?php

declare(strict_types=1);

namespace Hejunjie\PromotionEngine\Rules;

use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionResult;

/**
 * 第 N 件折扣规则：第 N 件打 Z 折
 *
 * 符合条件的第 N 件商品享受指定折扣。
 *
 * 示例：第 3 件 5 折（discountRate = 0.5）
 *
 * @package Hejunjie\PromotionEngine\Rules
 */
class NthItemDiscountRule extends AbstractPromotionRule
{
    /**
     * 第几件享受折扣
     *
     * @var int
     */
    protected int $nthItem;

    /**
     * 折扣率（0.8 表示 8 折）
     *
     * @var float
     */
    protected float $discountRate;

    /**
     * 构造函数
     *
     * @param int   $nthItem        第几件享受折扣
     * @param float $discountRate   折扣率（0.8 = 8折）
     * @param array $applicableTags 适用的商品标签，空数组表示不限标签
     * @param int   $priority       优先级（数值越小优先级越高）
     */
    public function __construct(int $nthItem, float $discountRate, array $applicableTags = [], int $priority = 1)
    {
        $this->nthItem = $nthItem;
        $this->discountRate = $discountRate;
        $this->applicableTags = $applicableTags;
        $this->priority = $priority;
    }

    /**
     * 应用第 N 件折扣规则
     *
     * 从符合条件的商品中取出第 N 件，计算其折扣金额。
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

        if (count($items) >= $this->nthItem) {
            $nthItem = $items[$this->nthItem - 1];
            $discountAmount = $nthItem['price'] * (1 - $this->discountRate);
            return new PromotionResult($discountAmount, "指定商品第{$this->nthItem}件打" . ($this->discountRate * 10) . "折");
        }

        return new PromotionResult(0, "未满足第{$this->nthItem}件折扣条件");
    }
}
