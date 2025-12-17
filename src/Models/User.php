<?php

namespace Hejunjie\PromotionEngine\Models;

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
