<?php

namespace Hejunjie\PromotionEngine;

/**
 * 促销结果类
 * @package Hejunjie\PromotionEngine
 */
class PromotionResult
{
    public function __construct(
        public float $discount,       // 优惠金额
        public string $description    // 优惠描述
    ) {}

    /**
     * 是否有优惠
     */
    public function hasDiscount(): bool
    {
        return $this->discount > 0;
    }

    /**
     * 获取优惠金额
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * 获取描述
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
