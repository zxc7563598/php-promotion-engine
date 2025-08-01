# 🛒 hejunjie/promotion-engine

<div align="center">
  <a href="./README.md">English</a>｜<a href="./README.zh-CN.md">简体中文</a>
  <hr width="50%"/>
</div>

一个 **灵活可扩展** 的 PHP 促销策略引擎，让购物车的各种复杂促销逻辑（满减、打折、阶梯优惠、会员折扣…）实现更优雅、可维护。

**本项目已经经由 Zread 解析完成，如果需要快速了解项目，可以点击此处进行查看：[了解本项目](https://zread.ai/zxc7563598/php-promotion-engine)**

---

## 📦 安装方式

使用 Composer 安装本库：

```bash
composer require hejunjie/promotion-engine
```

---

## 🚀 使用方式

```php
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
// 促销计算模式说明：
//
// independent（独立模式）：
//   每条规则基于「商品原价」单独计算优惠金额，
//   最后再统一汇总优惠，总价不会受到其他规则的中间计算结果影响。
//
// sequential（折上折模式）：
//   每条规则基于「上一次计算后的价格」继续计算，形成“折上折”效果。
//   当一条规则涉及多个商品时，会按商品原价在组合总价中的比例分摊优惠金额，
//   以确保每件商品的价格在后续计算中被正确更新。
//
// lock（锁定模式）：
//   每件商品最多只会被一条规则折扣，后续规则不会再对已被锁定的商品重复优惠。
//   适用于“某件商品只能享受一次优惠”的业务场景（如秒杀、专属券等）。
$engine->setMode('independent');

// 注册所有规则
$engine->addRule(new Rules\FullDiscountRule(200, 0.9));  // 满200打9折
$engine->addRule(new Rules\FullQuantityReductionRule(3, 20));  // 买满3件减20
$engine->addRule(new Rules\NthItemDiscountRule(3, 0.5));  // 第三件5折
$engine->addRule(new Rules\VipDiscountRule(0.95));  // VIP 95折
$engine->addRule(new Rules\FullQuantityDiscountRule(5, 0.9));  // 买满5件打9折
$engine->addRule(new Rules\FullReductionRule(100, 20));  // 满100减20
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

// 输出内容：
// === 测试结果 ===
// 原价: ¥540
// 优惠: -¥200
// 应付: ¥340
// 优惠明细:
// - 指定商品满200元打0.9折 (-¥54)
// - 指定商品满3件减20 (-¥20)
// - 指定商品第3件打5折 (-¥20)
// - VIP 0.95 折 (-¥27)
// - 指定商品满5件打9折 (-¥54)
// - 指定商品满100减20 (-¥20)
// - VIP 立减 5 元 (-¥5)

```

## 🛠️ 规则类型（开箱即用）

| 方法 | 说明 |
|:-----|:-----|
| FullDiscountRule | 满 X 元打 Z 折（例：满 200 打 8 折） | 
| FullQuantityReductionRule | 买满 X 件减 Y 元（例：买 3 件减 20 元） | 
| NthItemDiscountRule | 第 N 件打 Z 折（例：第 3 件 5 折） | 
| TieredDiscountRule | 阶梯满 X 元打 Z 折（例：满 100 打 9 折，满 200 打 8 折，满 500 打 7 折（取最高档）） | 
| VipDiscountRule | VIP 用户享受折扣（例：VIP 9 折） | 
| FullQuantityDiscountRule | 买满 X 件打 Z 折（例：买 3 件打 9 折） | 
| FullReductionRule | 满 X 元减 Y 元（例：满 100 减 20） | 
| NthItemReductionRule | 第 N 件特价（例：第 3 件 9.9 元） | 
| TieredReductionRule | 阶梯满 X 元减 Y 元（例：满 100 减 10，满 200 减 30，满 500 减 80（取最高档）） | 
| VipReductionRule | VIP 用户享受立减（例：VIP 下单减 5 元） | 

## 🎯 用途 & 初衷

> **为什么要写这个包？**

在电商、B2C、零售系统中，促销逻辑往往 **分散在 Controller / Service / if else** 里，规则一旦多起来，维护就成了灾难。

✅ 我希望 **用「规则引擎 + 可扩展的策略类」解决这个问题**：

- **每个优惠写成独立类** → 清晰、易测试、可复用
- **支持多种计算模式** → `original`​（基于原价） / `stack`​（折上折）
- **方便扩展** → 添加新规则只需要 `extends PromotionRule`​

👉 这样，未来接入 **新活动 / 会员等级 / 特定品类优惠** 只需新建一个 `Rule`​，无须大改代码。

---

## 🤝 欢迎 PR & 贡献

📢 **任何 PR 都欢迎：**

- 新的促销规则（比如优惠券、满赠、限时秒杀…）
- 优化核心逻辑（比如更灵活的规则优先级、优惠叠加策略）
- 单元测试（PHPUnit）或性能优化

👉 Fork 本仓库后提交 Pull Request，或提 issue 交流想法。
