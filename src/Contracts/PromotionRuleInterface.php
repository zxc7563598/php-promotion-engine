<?php

namespace Hejunjie\PromotionEngine\Contracts;

use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionResult;

/**
 * 促销规则接口
 * @package Hejunjie\PromotionEngine
 */
interface PromotionRuleInterface
{
    /**
     * 应用促销规则
     * 
     * @param Cart $cart 购物车对象
     * @param User $user 用户对象
     * 
     * @return PromotionResult 规则应用结果（包含优惠金额 & 描述）
     */
    public function apply(Cart $cart, User $user): PromotionResult;

    /**
     * 找出所有符合标签的商品下标
     * 
     * @param Cart $cart 
     * 
     * @return array 
     */
    public function getApplicableItems(Cart $cart): array;

    /**
     * 获取规则优先级
     * 
     * @return int 
     */
    public function getPriority(): int;

    /**
     * 获取适用的商品标签
     * 
     * @return array 
     */
    public function getApplicableTags(): array;
}