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
            'original_price' => $price
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
     */
    public function updateItemPrice(int $index, float $newPrice): void
    {
        $this->items[$index]['price'] = $newPrice;
    }

    /**
     * 批量减价（给满减/满折按比例分摊用）
     */
    public function applyDiscountToItems(array $indexes, float $totalDiscount): void
    {
        $shop_name = [];
        $shop_details = [];

        $totalPrice = 0;
        foreach ($indexes as $i) {
            $totalPrice += $this->items[$i]['price'] * $this->items[$i]['qty'];
        }
        foreach ($indexes as $i) {
            $item = &$this->items[$i];
            $shop_name[] = $item['name'];
            $share = round(($item['price'] * $item['qty']) / $totalPrice,4);
            $item['price'] -= round(($totalDiscount * $share) / $item['qty'], 2);
            $shop_details[] = [
                '名称' => $item['name'],
                '原价' => $item['original_price'],
                '现价' => $item['price'],
                '数量' => $item['qty'],
                '商品优惠价格中的占比' => $share*100 . '%',
                '优惠金额' => round($totalDiscount * $share, 2)
            ];
        }
        echo '[批量减价]: 商品 ' . json_encode($shop_name, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '减少金额' . $totalDiscount . "\n";
        echo '[批量减价]: ' . json_encode($shop_details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";
    }
}
