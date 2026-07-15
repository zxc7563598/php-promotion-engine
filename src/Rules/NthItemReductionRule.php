<?php

declare(strict_types=1);

namespace Hejunjie\PromotionEngine\Rules;

use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionResult;

/**
 * 第 N 件特价规则：第 N 件固定价格
 *
 * 符合条件的第 N 件商品按指定特价购买，超出原始价格的部分作为优惠。
 *
 * 示例：第 3 件 9.9 元
 *
 * @package Hejunjie\PromotionEngine\Rules
 */
class NthItemReductionRule extends AbstractPromotionRule
{
    /**
     * 第几件享受特价
     *
     * @var int
     */
    protected int $nthItem;

    /**
     * 特价金额（元）
     *
     * @var float
     */
    protected float $specialPrice;

    /**
     * 构造函数
     *
     * @param int   $nthItem        第几件享受特价
     * @param float $specialPrice   特价金额（元）
     * @param array $applicableTags 适用的商品标签，空数组表示不限标签
     * @param int   $priority       优先级（数值越小优先级越高）
     */
    public function __construct(int $nthItem, float $specialPrice, array $applicableTags = [], int $priority = 1)
    {
        $this->nthItem = $nthItem;
        $this->specialPrice = $specialPrice;
        $this->applicableTags = $applicableTags;
        $this->priority = $priority;
    }

    /**
     * 应用第 N 件特价规则
     *
     * 从符合条件的商品中取出第 N 件，计算其原始价格与特价之间的差价作为优惠。
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
            $discountAmount = $nthItem['price'] - $this->specialPrice;
            return new PromotionResult($discountAmount, "指定商品第{$this->nthItem}件特价{$this->specialPrice}元");
        }

        return new PromotionResult(0, "未满足第{$this->nthItem}件特价条件");
    }
}
