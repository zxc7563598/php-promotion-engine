# hejunjie/promotion-engine

[English](./README.md) ｜ 简体中文

一个灵活可扩展的 PHP 促销策略引擎，把购物车满减、打折、阶梯优惠、会员折扣等复杂促销逻辑从 if-else 中抽离出来，用可组合的规则类来管理。

**本项目已经经由 Zread 解析完成，如果需要快速了解项目，可以点击此处进行查看：[了解本项目](https://zread.ai/zxc7563598/php-promotion-engine)**

---

## 特性

- 🧩 **规则即类**：每种促销策略都是独立的规则类，清晰、可测试、可复用
- 🔀 **三种计算模式**：独立模式、折上折模式、锁定模式，覆盖不同业务场景
- 🏷️ **标签过滤**：规则可按商品标签精准匹配，轻松实现品类专属优惠
- 📊 **优先级控制**：规则按优先级排序执行，灵活控制优惠叠加顺序
- 🔌 **易于扩展**：继承 `AbstractPromotionRule` 即可添加自定义规则，无需改动引擎核心
- 🪶 **零依赖**：仅需 PHP >= 7.4，不依赖任何第三方库

## 环境要求

- PHP >= 7.4（同时支持 PHP 8.x）
- Composer

## 安装

```bash
composer require hejunjie/promotion-engine
```

## 快速开始

```php
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionEngine;
use Hejunjie\PromotionEngine\Rules;

// 创建 VIP 用户
$user = new User(true);

// 创建购物车
$cart = new Cart();
$cart->addItem('薯片', 30, 2, ['snacks']);
$cart->addItem('T恤', 100, 1, ['clothes']);
$cart->addItem('鼠标', 200, 1, ['electronics']);

// 初始化引擎并设置计算模式
$engine = new PromotionEngine();
$engine->setMode('independent');

// 注册规则
$engine->addRule(new Rules\FullReductionRule(300, 30));   // 满 300 减 30
$engine->addRule(new Rules\VipDiscountRule(0.95));         // VIP 95 折

// 执行计算
$result = $engine->calculate($cart, $user);

echo "原价: ¥{$result['original']}\n";   // 原价: ¥360
echo "优惠: -¥{$result['discount']}\n";  // 优惠: -¥48
echo "应付: ¥{$result['final']}\n";      // 应付: ¥312
print_r($result['details']);             // 优惠明细数组
```

## 计算模式

引擎支持三种计算模式，通过 `setMode()` 切换：

| 模式       | 标识          | 行为                                                                                 |
| :--------- | :------------ | :----------------------------------------------------------------------------------- |
| 独立模式   | `independent` | 每条规则基于**商品原价**独立计算优惠，互不影响，最后汇总                             |
| 折上折模式 | `sequential`  | 规则按顺序执行，前一条规则的优惠会分摊到商品价格，后续规则基于**已优惠价格**继续计算 |
| 锁定模式   | `lock`        | 每件商品最多被一条规则命中，已优惠的商品会被锁定，后续规则不再重复优惠               |

```php
// 独立模式：两条规则各算各的
$engine->setMode('independent');

// 折上折模式：先满减再打折
$engine->setMode('sequential');

// 锁定模式：每件商品只享受一次优惠
$engine->setMode('lock');
```

> [!NOTE]
> 独立模式下，规则顺序不影响最终优惠金额；折上折和锁定模式下，规则按优先级排序后依次执行。

## 内置规则

所有规则都支持通过最后两个可选参数指定**适用标签**和**优先级**：

```php
// 仅对 snacks 标签的商品生效，优先级为 10（越小越优先）
new Rules\FullReductionRule(100, 20, ['snacks'], 10);
```

| 规则                        | 说明                               | 示例                                                           |
| :-------------------------- | :--------------------------------- | :------------------------------------------------------------- |
| `FullReductionRule`         | 满 X 元减 Y 元                     | `new FullReductionRule(100, 20)`                               |
| `FullDiscountRule`          | 满 X 元打 Z 折                     | `new FullDiscountRule(200, 0.9)`                               |
| `FullQuantityReductionRule` | 满 X 件减 Y 元                     | `new FullQuantityReductionRule(3, 20)`                         |
| `FullQuantityDiscountRule`  | 满 X 件打 Z 折                     | `new FullQuantityDiscountRule(5, 0.9)`                         |
| `TieredDiscountRule`        | 阶梯折扣（满 X 打 Z 折，取最高档） | `new TieredDiscountRule([100 => 0.9, 200 => 0.8, 500 => 0.7])` |
| `TieredReductionRule`       | 阶梯满减（满 X 减 Y，取最高档）    | `new TieredReductionRule([100 => 10, 200 => 30, 500 => 80])`   |
| `NthItemDiscountRule`       | 第 N 件打 Z 折                     | `new NthItemDiscountRule(3, 0.5)`                              |
| `NthItemReductionRule`      | 第 N 件特价 X 元                   | `new NthItemReductionRule(3, 9.9)`                             |
| `VipDiscountRule`           | VIP 用户享折扣                     | `new VipDiscountRule(0.9)`                                     |
| `VipReductionRule`          | VIP 用户立减 X 元                  | `new VipReductionRule(5)`                                      |

## 进阶用法

### 标签过滤

通过商品标签让规则只对特定品类生效：

```php
$cart = new Cart();
$cart->addItem('可乐', 5, 10, ['drinks']);
$cart->addItem('键盘', 300, 1, ['electronics']);

// 仅饮料类商品满 3 件打 8 折
$engine->addRule(new Rules\FullQuantityDiscountRule(3, 0.8, ['drinks']));

// 仅电子产品满 200 减 50
$engine->addRule(new Rules\FullReductionRule(200, 50, ['electronics']));
```

### 自定义规则

继承 `AbstractPromotionRule` 并实现 `apply()` 方法：

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
            return new PromotionResult($discount, '生日专享 9 折');
        }

        return new PromotionResult(0, '生日折扣未触发');
    }
}

// 使用自定义规则
$engine->addRule(new BirthdayDiscountRule(['all'], 1));
```

### 优先级控制

数值越小优先级越高，默认为 1。在多规则场景下，优先级决定了执行顺序：

```php
// 先执行满减（优先级 5），再执行 VIP 折扣（优先级 10）
$engine->addRule(new Rules\FullReductionRule(300, 30, [], 5));
$engine->addRule(new Rules\VipDiscountRule(0.9, [], 10));
```

## 目录结构

```
src/
├── PromotionEngine.php          # 引擎入口，规则管理与计算调度
├── PromotionResult.php          # 规则计算结果（优惠金额 + 描述）
├── Calculators/
│   ├── IndependentCalculator.php  # 独立模式
│   ├── SequentialCalculator.php   # 折上折模式
│   └── LockCalculator.php         # 锁定模式
├── Contracts/
│   ├── PromotionCalculatorInterface.php  # 计算器接口
│   └── PromotionRuleInterface.php        # 规则接口
├── Models/
│   ├── Cart.php                 # 购物车模型
│   └── User.php                 # 用户模型
└── Rules/
    ├── AbstractPromotionRule.php       # 规则抽象基类
    ├── FullReductionRule.php           # 满减
    ├── FullDiscountRule.php            # 满折
    ├── FullQuantityReductionRule.php   # 满件减
    ├── FullQuantityDiscountRule.php    # 满件折
    ├── TieredDiscountRule.php          # 阶梯折扣
    ├── TieredReductionRule.php         # 阶梯满减
    ├── NthItemDiscountRule.php         # 第 N 件折扣
    ├── NthItemReductionRule.php        # 第 N 件特价
    ├── VipDiscountRule.php             # VIP 折扣
    └── VipReductionRule.php            # VIP 立减
```

## 贡献

欢迎提交 PR！以下方向尤其需要帮助：

- 新增促销规则（如优惠券、满赠、限时秒杀等）
- 单元测试
- 性能优化

Fork 本仓库后提交 Pull Request，或提 Issue 交流想法。
