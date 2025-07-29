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
        $this->items[] = compact('name', 'price', 'qty', 'tags');
        return $this;
    }

    /**
     * 获取购物车中的所有商品
     * 
     * @return float 
     */
    public function getTotal(): float
    {
        return array_reduce($this->items, fn($sum, $item) => $sum + $item['price'] * $item['qty'], 0);
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

        return array_filter($this->items, fn($item) => 
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
}
