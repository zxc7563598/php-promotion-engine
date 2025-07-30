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
     * @return PromotionResult 规则应用结果（包含优惠金额 & 描述）
     */
    public function apply(Cart $cart, User $user): PromotionResult;

    /** 优先级（数值越大越后执行，默认 0） */
    public function getPriority(): int;

    /** 适用的商品标签（空数组 = 所有商品） */
    public function getApplicableTags(): array;
}