<?php

namespace Hejunjie\PromotionEngine;

use Hejunjie\PromotionEngine\Contracts\PromotionCalculatorInterface;
use Hejunjie\PromotionEngine\Contracts\PromotionRuleInterface;
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\Calculators\IndependentCalculator;
use Hejunjie\PromotionEngine\Calculators\SequentialCalculator;
use Hejunjie\PromotionEngine\Calculators\LockCalculator;

/**
 * 促销引擎类
 * @package Hejunjie\PromotionEngine
 */
class PromotionEngine
{

    protected array $rules = [];

    protected PromotionCalculatorInterface $calculator;

    public function __construct()
    {
        $this->calculator = new IndependentCalculator();
    }

    public function setMode(string $mode): PromotionCalculatorInterface|\InvalidArgumentException
    {
        return match ($mode) {
            'independent' => $this->calculator = new IndependentCalculator(),
            'sequential'  => $this->calculator = new SequentialCalculator(),
            'lock'        => $this->calculator = new LockCalculator(),
            default       => throw new \InvalidArgumentException("未知模式: $mode"),
        };
    }

    public function addRule(PromotionRuleInterface $rule): self
    {
        $this->rules[] = $rule;
        return $this;
    }

    public function calculate(Cart $cart, User $user): array
    {
        return $this->calculator->calculate($cart, $user, $this->rules);
    }
}
