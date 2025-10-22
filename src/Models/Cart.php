<?php

namespace Hejunjie\PromotionEngine\Models;

class Cart
{
    protected array $items = [];

    /**
     * 添加商品到购物车
     *
     * @param string $name 商品名称
     * @param float $price 商品价格
     * @param int $qty 商品数量
     * @param array $tags 商品标签
     * 
     * @return self
     */
    public function addItem(string $name, float $price, int $qty = 1, array $tags = []): self
    {
        $this->items[] = [
            'name' => $name,
            'price' => $price,
            'qty' => $qty,
            'tags' => $tags,
            'original_price' => $price, // 保留原价以便计算折扣
            'locked' => false // 锁定状态，防止重复折扣
        ];
        return $this;
    }

    /**
     * 获取购物车中的所有商品价格
     * 
     * @return float 
     */
    public function getTotal(): float
    {
        return array_reduce($this->items, fn($sum, $item) => $sum + $item['price'] * $item['qty'], 0);
    }

    /**
     * 获取购物车中的所有商品原价
     * 
     * @return float 
     */
    public function getOriginalTotal(): float
    {
        return array_reduce($this->items, fn($sum, $item) => $sum + $item['original_price'] * $item['qty'], 0);
    }

    /**
     * 获取购物车中商品的数量
     * 
     * @return int 
     */
    public function getItemCount(): int
    {
        return array_reduce($this->items, fn($sum, $item) => $sum + $item['qty'], 0);
    }

    /**
     * 根据标签过滤商品
     * 
     * @param array $tags 标签数组
     * 
     * @return array 
     */
    public function filterItemsByTags(array $tags): array
    {
        if (empty($tags)) return $this->items;

        return array_filter(
            $this->items,
            fn($item) =>
            count(array_intersect($tags, $item['tags'])) > 0
        );
    }

    /**
     * 计算购物车中商品的总价
     * 
     * @param array $items 商品数组
     * 
     * @return float 
     */
    public function calculateItemsTotal(array $items): float
    {
        return array_reduce($items, fn($sum, $item) => $sum + $item['price'] * $item['qty'], 0);
    }

    /**
     * 获取全部的商品
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * 动态修改指定商品价格
     * 
     * @param int $index 
     * @param float $newPrice 
     * 
     * @return void 
     */
    public function updateItemPrice(int $index, float $newPrice): void
    {
        $this->items[$index]['price'] = $newPrice;
    }

    /**
     * 批量减价（给满减/满折按比例分摊用）
     * 
     * @param array $indexes 商品下标
     * @param float $totalDiscount 优惠价格
     * 
     * @return void 
     */
    public function applyDiscountToItems(array $indexes, float $totalDiscount): void
    {
        if (empty($indexes) || $totalDiscount <= 0) {
            return;
        }
        $totalPrice = 0.0;
        foreach ($indexes as $i) {
            $totalPrice += $this->items[$i]['price'] * $this->items[$i]['qty'];
        }
        if ($totalPrice <= 0) {
            return;
        }

        $distributed = 0.0;
        $lastIndex = $indexes[array_key_last($indexes)];
        foreach ($indexes as $i) {
            $item = &$this->items[$i];
            $share = ($item['price'] * $item['qty']) / $totalPrice;
            $unitReduction = round(($totalDiscount * $share) / $item['qty'], 2);
            $distributed += $unitReduction * $item['qty'];
            $item['price'] = max(0, round($item['price'] - $unitReduction, 2));
            unset($item);
        }

        // 尾差补偿，保证分摊总额与 totalDiscount 一致
        $remainder = round($totalDiscount - $distributed, 2);
        if (abs($remainder) > 0) {
            $qty = $this->items[$lastIndex]['qty'];
            $this->items[$lastIndex]['price'] = max(0, round($this->items[$lastIndex]['price'] - ($remainder / $qty), 2));
        }
    }

    /**
     * 锁定指定商品，防止重复折扣
     * 
     * @param array $indexes 商品下标
     * 
     * @return void 
     */
    public function lockItems(array $indexes): void
    {
        foreach ($indexes as $i) {
            $this->items[$i]['locked'] = true;
        }
    }

    /**
     * 检查指定商品是否被锁定
     * 
     * @param int $index 商品下标
     * 
     * @return bool 
     */
    public function isLocked(int $index): bool
    {
        return $this->items[$index]['locked'] ?? false;
    }
}
