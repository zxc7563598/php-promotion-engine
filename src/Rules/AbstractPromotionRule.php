<?php

declare(strict_types=1);

namespace Hejunjie\PromotionEngine\Rules;

use Hejunjie\PromotionEngine\Contracts\PromotionRuleInterface;
use Hejunjie\PromotionEngine\Models\Cart;
use Hejunjie\PromotionEngine\Models\User;
use Hejunjie\PromotionEngine\PromotionResult;

/**
 * 促销规则抽象基类
 *
 * 提供所有规则共用的标签过滤、优先级获取等样板方法，
 * 子类只需关注 apply() 中的具体促销逻辑。
 *
 * @package Hejunjie\PromotionEngine\Rules
 */
abstract class AbstractPromotionRule implements PromotionRuleInterface
{
    /**
     * 适用的商品标签（空数组表示不限标签）
     *
     * @var array
     */
    protected array $applicableTags;

    /**
     * 规则优先级（数值越小优先级越高）
     *
     * @var int
     */
    protected int $priority;

    /**
     * 应用促销规则
     *
     * @param Cart  $cart             购物车对象
     * @param User  $user             用户对象
     * @param array $eligibleIndexes  符合条件的商品下标列表（锁模式使用）
     *
     * @return PromotionResult 规则应用结果
     */
    abstract public function apply(Cart $cart, User $user, array $eligibleIndexes = []): PromotionResult;

    /**
     * 找出购物车中所有符合标签的商品下标
     *
     * 遍历购物车全部商品，如果 applicableTags 为空则返回所有商品下标，
     * 否则仅返回标签有交集的商品下标。
     *
     * @param Cart $cart 购物车对象
     *
     * @return int[] 符合条件的商品下标数组
     */
    public function getApplicableItems(Cart $cart): array
    {
        $items = $cart->getItems();
        $indexes = [];
        foreach ($items as $i => $item) {
            if (empty($this->applicableTags) || count(array_intersect($this->applicableTags, $item['tags'])) > 0) {
                $indexes[] = $i;
            }
        }
        return $indexes;
    }

    /**
     * 获取规则优先级
     *
     * @return int 优先级数值
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * 获取适用的商品标签
     *
     * @return array 标签数组
     */
    public function getApplicableTags(): array
    {
        return $this->applicableTags;
    }

    /**
     * 根据锁模式筛选符合条件的商品
     *
     * 非锁模式（eligibleIndexes 为空）时返回全部符合标签的商品；
     * 锁模式下仅返回在 eligibleIndexes 中的符合标签的商品。
     *
     * @param Cart  $cart             购物车对象
     * @param array $eligibleIndexes  锁模式下的可选商品下标
     *
     * @return array 筛选后的商品数组
     */
    protected function filterEligibleItems(Cart $cart, array $eligibleIndexes): array
    {
        $tagged = $cart->filterItemsByTags($this->applicableTags);

        if (empty($eligibleIndexes)) {
            return $tagged;
        }

        return array_intersect_key($tagged, array_flip($eligibleIndexes));
    }
}
