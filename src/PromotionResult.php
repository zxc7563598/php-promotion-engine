<?php

declare(strict_types=1);

namespace Hejunjie\PromotionEngine;

/**
 * 促销计算结果
 *
 * 封装单条规则应用后返回的优惠金额和描述信息。
 * 金额单位为元，支持两位小数精度。
 *
 * @package Hejunjie\PromotionEngine
 */
class PromotionResult
{
    public float $discount;       // 优惠金额
    public string $description;    // 优惠描述

    public function __construct(float $discount, string $description)
    {
        $this->discount = $discount;
        $this->description = $description;
    }

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
