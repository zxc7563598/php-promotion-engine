# hejunjie/promotion-engine

English ｜ [简体中文](./README.zh-CN.md)

A flexible and extensible PHP promotion strategy engine that abstracts complex shopping cart promotion logic — money-off, discounts, tiered offers, member discounts — out of if-else spaghetti and into composable rule classes.

**This project has been parsed by Zread. If you need a quick overview of the project, you can click here to view it：[Understand this project](https://zread.ai/zxc7563598/php-promotion-engine)**

---

## Features

- 🧩 **Rules as classes**：Each promotion strategy is an independent rule class — clear, testable, and reusable
- 🔀 **Three calculation modes**：Independent, sequential (stacked), and lock modes for different business scenarios
- 🏷️ **Tag filtering**：Rules match items by product tags, making category-specific promotions effortless
- 📊 **Priority control**：Rules execute in priority order, giving you full control over discount stacking
- 🔌 **Easy to extend**：Add custom rules by extending `AbstractPromotionRule` — no changes to the engine core
- 🪶 **Zero dependencies**：Requires only PHP >= 7.4, no third-party libraries

## Requirements

- PHP >= 7.4 (also supports PHP 8.x)
- Composer

## Installation

```bash
composer require hejunjie/promotion-engine
```

## Quick Start

```php
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionEngine;
use Hejunjie\PromotionEngine\Rules;

// Create a VIP user
$user = new User(true);

// Create a shopping cart
$cart = new Cart();
$cart->addItem('Chips', 30, 2, ['snacks']);
$cart->addItem('T-shirt', 100, 1, ['clothes']);
$cart->addItem('Mouse', 200, 1, ['electronics']);

// Initialize the engine and set calculation mode
$engine = new PromotionEngine();
$engine->setMode('independent');

// Register rules
$engine->addRule(new Rules\FullReductionRule(300, 30));   // ¥30 off when total ≥ ¥300
$engine->addRule(new Rules\VipDiscountRule(0.95));         // VIP 5% off

// Calculate
$result = $engine->calculate($cart, $user);

echo "Original: ¥{$result['original']}\n";   // Original: ¥360
echo "Discount: -¥{$result['discount']}\n";  // Discount: -¥48
echo "Final: ¥{$result['final']}\n";         // Final: ¥312
print_r($result['details']);                 // Discount detail array
```

## Calculation Modes

The engine supports three calculation modes, switchable via `setMode()`：

| Mode        | Identifier    | Behavior                                                                                                                                                                   |
| :---------- | :------------ | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Independent | `independent` | Each rule calculates its discount against the **original price** independently, then all discounts are summed                                                              |
| Sequential  | `sequential`  | Rules execute in order. Each rule's discount is distributed back into item prices, so subsequent rules calculate against **already-discounted prices** (stacked discounts) |
| Lock        | `lock`        | Each item can be discounted by at most one rule. Discounted items are locked and skipped by subsequent rules                                                               |

```php
// Independent: each rule works against original prices
$engine->setMode('independent');

// Sequential: discount-on-discount
$engine->setMode('sequential');

// Lock: each item receives only one discount
$engine->setMode('lock');
```

> [!NOTE]
> In independent mode, rule order does not affect the final discount. In sequential and lock modes, rules execute in priority order.

## Built-in Rules

All rules accept two optional trailing parameters for **tag filtering** and **priority**：

```php
// Only applies to items tagged 'snacks', priority 10 (lower = higher priority)
new Rules\FullReductionRule(100, 20, ['snacks'], 10);
```

| Rule                        | Description                              | Example                                                        |
| :-------------------------- | :--------------------------------------- | :------------------------------------------------------------- |
| `FullReductionRule`         | Spend ¥X, get ¥Y off                     | `new FullReductionRule(100, 20)`                               |
| `FullDiscountRule`          | Spend ¥X, get Z% off                     | `new FullDiscountRule(200, 0.9)`                               |
| `FullQuantityReductionRule` | Buy X items, get ¥Y off                  | `new FullQuantityReductionRule(3, 20)`                         |
| `FullQuantityDiscountRule`  | Buy X items, get Z% off                  | `new FullQuantityDiscountRule(5, 0.9)`                         |
| `TieredDiscountRule`        | Tiered discount (highest eligible tier)  | `new TieredDiscountRule([100 => 0.9, 200 => 0.8, 500 => 0.7])` |
| `TieredReductionRule`       | Tiered money-off (highest eligible tier) | `new TieredReductionRule([100 => 10, 200 => 30, 500 => 80])`   |
| `NthItemDiscountRule`       | Nth item at Z% off                       | `new NthItemDiscountRule(3, 0.5)`                              |
| `NthItemReductionRule`      | Nth item at special price ¥X             | `new NthItemReductionRule(3, 9.9)`                             |
| `VipDiscountRule`           | VIP members get Z% off                   | `new VipDiscountRule(0.9)`                                     |
| `VipReductionRule`          | VIP members get ¥X off                   | `new VipReductionRule(5)`                                      |

## Advanced Usage

### Tag Filtering

Use product tags to restrict rules to specific categories：

```php
$cart = new Cart();
$cart->addItem('Cola', 5, 10, ['drinks']);
$cart->addItem('Keyboard', 300, 1, ['electronics']);

// Only drinks: 20% off when buying 3 or more
$engine->addRule(new Rules\FullQuantityDiscountRule(3, 0.8, ['drinks']));

// Only electronics: ¥50 off when spending ¥200
$engine->addRule(new Rules\FullReductionRule(200, 50, ['electronics']));
```

### Custom Rules

Extend `AbstractPromotionRule` and implement the `apply()` method：

```php
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionResult;
use Hejunjie\PromotionEngine\Rules\AbstractPromotionRule;

class BirthdayDiscountRule extends AbstractPromotionRule
{
    public function apply(Cart $cart, User $user, array $eligibleIndexes = []): PromotionResult
    {
        $items = $this->filterEligibleItems($cart, $eligibleIndexes);
        $total = $cart->calculateItemsTotal($items);

        if ($total > 0) {
            $discount = round($total * 0.1, 2);
            return new PromotionResult($discount, 'Birthday 10% off');
        }

        return new PromotionResult(0, 'Birthday discount not triggered');
    }
}

// Use the custom rule
$engine->addRule(new BirthdayDiscountRule(['all'], 1));
```

### Priority Control

Lower numbers have higher priority (default is 1). Priority determines execution order in multi-rule scenarios：

```php
// Money-off runs first (priority 5), then VIP discount (priority 10)
$engine->addRule(new Rules\FullReductionRule(300, 30, [], 5));
$engine->addRule(new Rules\VipDiscountRule(0.9, [], 10));
```

## Directory Structure

```
src/
├── PromotionEngine.php             # Engine entry point, rule management and calculation dispatch
├── PromotionResult.php             # Rule result value object (discount amount + description)
├── Calculators/
│   ├── IndependentCalculator.php   # Independent mode
│   ├── SequentialCalculator.php    # Sequential (stacked) mode
│   └── LockCalculator.php          # Lock mode
├── Contracts/
│   ├── PromotionCalculatorInterface.php  # Calculator interface
│   └── PromotionRuleInterface.php        # Rule interface
├── Models/
│   ├── Cart.php                    # Shopping cart model
│   └── User.php                    # User model
└── Rules/
    ├── AbstractPromotionRule.php         # Abstract rule base class
    ├── FullReductionRule.php             # Spend X, get Y off
    ├── FullDiscountRule.php              # Spend X, get Z% off
    ├── FullQuantityReductionRule.php     # Buy X items, get Y off
    ├── FullQuantityDiscountRule.php      # Buy X items, get Z% off
    ├── TieredDiscountRule.php            # Tiered discount
    ├── TieredReductionRule.php           # Tiered money-off
    ├── NthItemDiscountRule.php           # Nth item discount
    ├── NthItemReductionRule.php          # Nth item special price
    ├── VipDiscountRule.php               # VIP discount
    └── VipReductionRule.php              # VIP money-off
```

## Contributing

PRs are welcome! Areas that especially need help：

- New promotion rules (coupons, buy-one-get-one, flash sales, etc.)
- Unit tests
- Performance improvements

Fork the repository, submit a Pull Request, or open an Issue to discuss ideas.
