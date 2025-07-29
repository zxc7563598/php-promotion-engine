# 🛒 hejunjie/promotion-engine

<div align="center">
  <a href="./README.md">English</a>｜<a href="./README.zh-CN.md">简体中文</a>
  <hr width="50%"/>
</div>

A **flexible and scalable** PHP promotional strategy engine that enables various complex promotional logics (money off, discounts, tiered offers, member discounts, etc.) in shopping carts to be implemented more elegantly and maintainably.

**This project has been parsed by Zread. If you need a quick overview of the project, you can click here to view it：[Understand this project](https://zread.ai/zxc7563598/php-promotion-engine)**

---

## 📦 Installation method

Install this library using Composer：

```bash
composer require hejunjie/promotion-engine
```

---

## 🚀 Usage

```php
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionEngine;
use Hejunjie\PromotionEngine\Rules;

// Create user (VIP)
$user = new User(vip: true);

// Create a shopping cart (with 10 items, and the total price can cover various rules)
$cart = new Cart();
$cart->addItem('商品A', 50, 1, ['snacks']);
$cart->addItem('商品B', 60, 1, ['clothes']);
$cart->addItem('商品C', 40, 1, ['clothes']);
$cart->addItem('商品D', 100, 1, ['promo']);
$cart->addItem('商品E', 30, 3, ['snacks']);
$cart->addItem('商品F', 200, 1, ['electronics']);

// Initialize the promotion engine
$engine = new PromotionEngine();

// Register all rules
$engine->addRule(new Rules\FullDiscountRule(200, 0.9));  // 10% off for purchases over 200
$engine->addRule(new Rules\FullQuantityReductionRule(3, 20));  // Buy 3 or more and get 20% off
$engine->addRule(new Rules\NthItemDiscountRule(3, 0.5));  // Third item is 50% off
$engine->addRule(new Rules\TieredDiscountRule([
    100 => 0.95,
    300 => 0.9,
    500 => 0.85
]));  // Ladder full fold
$engine->addRule(new Rules\VipDiscountRule(0.95));  // 5% discount for VIP
$engine->addRule(new Rules\FullQuantityDiscountRule(5, 0.9));  // 10% off for purchases of 5 items or more
$engine->addRule(new Rules\FullReductionRule(100, 20));  // 20 off for purchases over 100
$engine->addRule(new Rules\NthItemReductionRule(3, 9.9));  // The third item is priced at 9.9 yuan
$engine->addRule(new Rules\TieredReductionRule([
    100 => 10,
    200 => 30,
    500 => 80
]));  // Ladder full reduction
$engine->addRule(new Rules\VipReductionRule(5));  // VIP: 5 yuan off

// Perform calculation
$result = $engine->calculate($cart, $user);

// Print result (for manual verification)
echo "\n=== test result ===\n";
echo "original price: ¥{$result['original']}\n";
echo "discount: -¥{$result['discount']}\n";
echo "final: ¥{$result['final']}\n";
echo "Discount Details:\n";
foreach ($result['details'] as $detail) {
    echo "- {$detail}\n";
}

// 输出内容：
// === test result ===
// original price: ¥540
// discount: -¥391.1
// final: ¥148.9
// Discount Details:
// - 指定商品满200元打0.9折 (-¥54)
// - 指定商品满3件减20 (-¥20)
// - 指定商品第3件打5折 (-¥20)
// - 指定商品满500元打8.5折 (-¥81)
// - VIP 0.95 折 (-¥27)
// - 指定商品满5件打9折 (-¥54)
// - 指定商品满100减20 (-¥20)
// - 指定商品第3件特价9.9元 (-¥30.1)
// - 指定商品满500减80 (-¥80)
// - VIP 立减 5 元 (-¥5)

```

## 🛠️ Rule type (out-of-box)

| 方法 | 说明 |
|:-----|:-----|
| FullDiscountRule | For purchases over X yuan, receive a Z% discount (Example: For purchases over 200 yuan, receive a 20% discount) | 
| FullQuantityReductionRule | Buy X items and get Y yuan off (Example: Buy 3 items and get 20 yuan off) | 
| NthItemDiscountRule | The Nth item is discounted by Z (Example: the 3rd item is discounted by 50%) | 
| TieredDiscountRule | Enjoy a Z% discount when your purchase reaches X yuan (Example: 10% discount when your purchase reaches 100 yuan, 20% discount when your purchase reaches 200 yuan, 30% discount when your purchase reaches 500 yuan (whichever is the highest)) | 
| VipDiscountRule | VIP users enjoy discounts (Example: 10% off for VIP) | 
| FullQuantityDiscountRule | Buy X items and get Z% off (Example: Buy 3 items and get 10% off) | 
| FullReductionRule | For purchases over X yuan, get Y yuan off (Example: for purchases over 100 yuan, get 20 yuan off)" | 
| NthItemReductionRule | The Nth item is on special offer (Example: the 3rd item is priced at 9.9 yuan) | 
| TieredReductionRule | Step discount: subtract Y yuan when the purchase reaches X yuan (Example: subtract 10 yuan when the purchase reaches 100 yuan, subtract 30 yuan when the purchase reaches 200 yuan, and subtract 80 yuan when the purchase reaches 500 yuan (whichever is the highest level)) | 
| VipReductionRule | VIP users enjoy immediate discounts (Example: a discount of 5 yuan when ordering as a VIP) | 

## 🎯 Purpose & Original Intent

> **Why write this package?**

In e-commerce, B2C, and retail systems, promotion logic is often scattered across Controller, Service, and if-else statements. Once there are numerous rules, maintenance becomes a nightmare.

✅ I hope to **solve this problem using a 'rule engine + extensible policy class'**:

- **Write each discount as a separate class** → clear, easy to test, reusable
- **Supports multiple calculation modes** → `original` (based on original price) / `stack` (discount on discount)
- **Easy to extend** → Adding new rules only requires `extends PromotionRule`

👉 In this way, for future integrations of **new activities / membership levels / specific product category discounts**, it will only be necessary to create a new `Rule`, without the need for major code modifications.

---

## 🤝 Welcome PR & contributions

📢 **All pull requests (PRs) are welcome:**

- New promotional rules (such as coupons, buy-one-get-one-free, flash sales, etc.)
- Optimize core logic (such as more flexible rule priorities, discount stacking strategies)
- Unit testing (PHPUnit) or performance optimization
- 
👉 Fork this repository and submit a Pull Request, or create an issue to exchange ideas.
