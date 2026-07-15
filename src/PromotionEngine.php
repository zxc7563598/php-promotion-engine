<?php

declare(strict_types=1);

namespace Hejunjie\PromotionEngine;

use Hejunjie\PromotionEngine\Contracts\PromotionCalculatorInterface;
use Hejunjie\PromotionEngine\Contracts\PromotionRuleInterface;
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\Calculators\IndependentCalculator;
use Hejunjie\PromotionEngine\Calculators\SequentialCalculator;
use Hejunjie\PromotionEngine\Calculators\LockCalculator;

/**
 * 促销引擎
 *
 * 核心调度类，负责管理促销规则并委托给计算策略执行。
 * 支持三种计算模式：独立模式、折上折模式、锁定模式。
 *
 * 使用示例：
 * ```php
 * $engine = new PromotionEngine();
 * $engine->setMode('sequential')
 *        ->addRule(new FullReductionRule(100, 10))
 *        ->addRule(new VipDiscountRule(0.9));
 * $results = $engine->calculate($cart, $user);
 * ```
 *
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

    /**
     * 注册模式
     * 
     * 注册不同的模式，来决定促销规则应该被如何执行
     * - independent：独立模式 - 顺序执行规则时，每条规则都基于商品原价计算优惠金额，最后统一汇总优惠金额
     * - sequential：折上折模式 - 顺序执行规则时，每条规则计算完商品折扣后，下一条规则基于上条规则优惠后的金额计算折扣金额
     * - lock：锁定模式 - 顺序执行规则时，每个商品只会在第一次满足规则时进行折扣，商品被折扣后即使满足后续规则也不会进行折扣
     *
     * @param string $mode 模式(independent/sequential/lock)
     * 
     * @return PromotionCalculatorInterface
     */
    public function setMode(string $mode): PromotionCalculatorInterface
    {
        switch ($mode) {
            case 'independent': // 独立模式
                $this->calculator = new IndependentCalculator();
                return $this->calculator;
            case 'sequential': // 折上折模式
                $this->calculator = new SequentialCalculator();
                return $this->calculator;
            case 'lock': // 锁定模式
                $this->calculator = new LockCalculator();
                return $this->calculator;
            default:
                throw new \InvalidArgumentException("未知模式: $mode");
        }
    }

    /**
     * 注册规则
     * 
     * 规则会在指定的模式中顺序执行，实际效果由注册的模式决定
     *
     * @param PromotionRuleInterface $rule 促销规则
     * 
     * @return self
     */
    public function addRule(PromotionRuleInterface $rule): self
    {
        $this->rules[] = $rule;
        return $this;
    }

    /**
     * 计算折扣
     * 
     * 根据注册好的规则模式以及注册好的规则对用户购买的商品进行计算
     *
     * @param Cart $cart 购物车模型
     * @param User $user 用户模型
     * 
     * @return array
     */
    public function calculate(Cart $cart, User $user): array
    {
        return $this->calculator->calculate($cart, $user, $this->rules);
    }
}
