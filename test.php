<?php

require 'vendor/autoload.php';

use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionEngine;
use Hejunjie\PromotionEngine\Rules;

// 创建用户（VIP）
$user = new User(vip: true);

// 创建购物车（10 件商品，总价可覆盖各种规则）
$cart = new Cart();
$cart->addItem('商品A', 50, 1, ['snacks']);
$cart->addItem('商品B', 60, 1, ['clothes']);
$cart->addItem('商品C', 40, 1, ['clothes']);
$cart->addItem('商品D', 100, 1, ['promo']);
$cart->addItem('商品E', 30, 3, ['snacks']);
$cart->addItem('商品F', 200, 1, ['electronics']);

// 初始化促销引擎
$engine = new PromotionEngine();

// 注册所有规则
$engine->addRule(new Rules\FullDiscountRule(200, 0.9));  // 满200打9折
$engine->addRule(new Rules\FullQuantityReductionRule(3, 20));  // 买满3件减20
$engine->addRule(new Rules\NthItemDiscountRule(3, 0.5));  // 第三件5折
$engine->addRule(new Rules\TieredDiscountRule([
    100 => 0.95,
    300 => 0.9,
    500 => 0.85
]));  // 阶梯满折
$engine->addRule(new Rules\VipDiscountRule(0.95));  // VIP 95折
$engine->addRule(new Rules\FullQuantityDiscountRule(5, 0.9));  // 买满5件打9折
$engine->addRule(new Rules\FullReductionRule(100, 20));  // 满100减20
$engine->addRule(new Rules\NthItemReductionRule(3, 9.9));  // 第三件9.9元
$engine->addRule(new Rules\TieredReductionRule([
    100 => 10,
    200 => 30,
    500 => 80
]));  // 阶梯满减
$engine->addRule(new Rules\VipReductionRule(5));  // VIP立减5元

// 执行计算
$result = $engine->calculate($cart, $user);

// 6️⃣ 打印结果（便于人工验证）
echo "\n=== 测试结果 ===\n";
echo "原价: ¥{$result['original']}\n";
echo "优惠: -¥{$result['discount']}\n";
echo "应付: ¥{$result['final']}\n";
echo "优惠明细:\n";
foreach ($result['details'] as $detail) {
    echo "- {$detail}\n";
}
