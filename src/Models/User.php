<?php

declare(strict_types=1);

namespace Hejunjie\PromotionEngine\Models;

/**
 * 用户模型
 *
 * 代表下单用户，包含 VIP 身份标识等基本信息。
 *
 * @package Hejunjie\PromotionEngine\Models
 */
class User
{
    protected bool $vip;

    public function __construct(bool $vip = false)
    {
        $this->vip = $vip;
    }

    /**
     * 检查用户是否为VIP
     * 
     * @return bool 
     */
    public function isVip(): bool
    {
        return $this->vip;
    }
}
