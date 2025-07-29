<?php

namespace Hejunjie\PromotionEngine\Models;

class User
{
    public function __construct(protected bool $vip = false) {}

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
