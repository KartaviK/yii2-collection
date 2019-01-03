<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Kartavik\Yii\Collection;

use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

/**
 * Class Collection
 * @package Kartavik\Yii\Collection
 *
 * Collection is a container for a set of items.
 *
 * It provides methods for transforming and filtering the items as well as sorting methods, which can be applied
 * using a chained interface. All these operations will return a new collection containing the modified data
 * keeping the original collection as it was as long as containing objects state is not changed.
 *
 * ```php
 * $collection = new Collection([1, 2, 3]);
 * echo $collection->map(function($i) { // [2, 3, 4]
 *     return $i + 1;
 * })->filter(function($i) { // [2, 3]
 *     return $i < 4;
 * })->sum(); // 5
 * ```
 *
 * The collection implements [[ArrayAccess]], [[Iterator]], and [[Countable]], so you can access it in
 * the same way you use a PHP array. A collection however is read-only, you can not manipulate single items.
 *
 * ```php
 * $collection = new Collection([1, 2, 3]);
 * echo $collection[1]; // 2
 * foreach($collection as $item) {
 *     echo $item . ' ';
 * } // will print 1 2 3
 * ```
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @author Roman Varkuta <roman.varkuta@gmail.com>
 * @since 1.0
 */
class Collection extends Component implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable
{
    /** @var array Data contained in this collection. */
    private $items;

    public function __construct(iterable $items = [], array $config = [])
    {
        $this->items = $items;

        parent::__construct($config);
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Apply callback to all items in the collection.
     *
     * The original collection will not be changed, a new collection with modified data is returned.
     *
     * @param \Closure $callback
     *
     * @return Collection|static A new collection with items returned from the callback.
     */
    public function map(\Closure $callback): Collection
    {
        return new static(\array_map($callback, $this->items));
    }

    /**
     * Filter items from the collection.
     *
     * The original collection will not be changed, a new collection with modified data is returned.
     *
     * @param \Closure $callable the callback function to decide which items to remove. Signature: `function($model,
     *     $key)`. Should return `true` to keep an item and return `false` to remove them.
     *
     * @return static a new collection containing the filtered items.
     */
    public function filter(\Closure $callable): Collection
    {
        return new static(\array_filter($this->items, $callable, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Apply reduce operation to items from the collection.
     *
     * @param \Closure $callable the callback function to compute the reduce value. Signature: `function($carry,
     *     $model)`.
     * @param mixed $initialValue Initial value to pass to the callback on first item.
     *
     * @return mixed The result of the reduce operation.
     */
    public function reduce(\Closure $callable, $initialValue = null)
    {
        return \array_reduce($this->items, $callable, $initialValue);
    }

    /**
     * Calculate the sum of a field of the models in the collection.
     *
     * @param string|\Closure|array $field the name of the field to calculate.
     * This will be passed to [[ArrayHelper::getValue()]].
     *
     * @return int|float|double The calculated sum.
     */
    public function sum($field = null)
    {
        return $this->reduce(function ($carry, $model) use ($field) {
            return $carry + ($field === null ? $model : ArrayHelper::getValue($model, $field, 0));
        }, 0);
    }

    /**
     * Calculate the maximum value of a field of the models in the collection
     *
     * @param string|\Closure|array $field the name of the field to calculate.
     * This will be passed to [[ArrayHelper::getValue()]].
     *
     * @return int|float|double the calculated maximum value. 0 if the collection is empty.
     */
    public function max($field = null)
    {
        return $this->reduce(function ($carry, $model) use ($field) {
            $value = ($field === null ? $model : ArrayHelper::getValue($model, $field, 0));
            if ($carry === null) {
                return $value;
            }
            return $value > $carry ? $value : $carry;
        });
    }

    /**
     * Calculate the minimum value of a field of the models in the collection
     *
     * @param string|\Closure|array $field the name of the field to calculate.
     * This will be passed to [[ArrayHelper::getValue()]].
     *
     * @return int|float|double the calculated minimum value. 0 if the collection is empty.
     */
    public function min($field = null)
    {
        return $this->reduce(function ($carry, $model) use ($field) {
            $value = ($field === null ? $model : ArrayHelper::getValue($model, $field, 0));
            if ($carry === null) {
                return $value;
            }
            return $value < $carry ? $value : $carry;
        });
    }

    /**
     * Count items in this collection.
     * @return int the count of items in this collection.
     */
    public function count(): int
    {
        return \count($this->items);
    }

    /**
     * Sort collection data by value.
     *
     * If the collection values are not scalar types, use [[sortBy()]] instead.
     *
     * The original collection will not be changed, a new collection with sorted data is returned.
     *
     * @param int $direction sort direction, either `SORT_ASC` or `SORT_DESC`.
     * @param int $sortFlag type of comparison, either `SORT_REGULAR`, `SORT_NUMERIC`, `SORT_STRING`,
     * `SORT_LOCALE_STRING`, `SORT_NATURAL` or `SORT_FLAG_CASE`.
     * See [the PHP manual](http://php.net/manual/en/function.sort.php#refsect1-function.sort-parameters)
     * for details.
     *
     * @return static a new collection containing the sorted items.
     * @see http://php.net/manual/en/function.asort.php
     * @see http://php.net/manual/en/function.arsort.php
     */
    public function sort(int $direction = SORT_ASC, int $sortFlag = SORT_REGULAR): Collection
    {
        $items = $this->items;

        if ($direction === SORT_ASC) {
            \asort($items, $sortFlag);
        } else {
            \arsort($items, $sortFlag);
        }

        return new static($items);
    }

    /**
     * Sort collection data by key.
     *
     * The original collection will not be changed, a new collection with sorted data is returned.
     *
     * @param int $direction sort direction, either `SORT_ASC` or `SORT_DESC`.
     * @param int $sortFlag type of comparison, either `SORT_REGULAR`, `SORT_NUMERIC`, `SORT_STRING`,
     * `SORT_LOCALE_STRING`, `SORT_NATURAL` or `SORT_FLAG_CASE`.
     * See [the PHP manual](http://php.net/manual/en/function.sort.php#refsect1-function.sort-parameters)
     * for details.
     *
     * @return static a new collection containing the sorted items.
     * @see http://php.net/manual/en/function.ksort.php
     * @see http://php.net/manual/en/function.krsort.php
     */
    public function sortByKey(int $direction = SORT_ASC, int $sortFlag = SORT_REGULAR): Collection
    {
        $items = $this->items;

        if ($direction === SORT_ASC) {
            \ksort($items, $sortFlag);
        } else {
            \krsort($items, $sortFlag);
        }

        return new static($items);
    }

    /**
     * Sort collection data by value using natural sort comparison.
     *
     * If the collection values are not scalar types, use [[sortBy()]] instead.
     *
     * The original collection will not be changed, a new collection with sorted data is returned.
     *
     * @param bool $caseSensitive whether comparison should be done in a case-sensitive manner. Defaults to `false`.
     *
     * @return static a new collection containing the sorted items.
     * @see http://php.net/manual/en/function.natsort.php
     * @see http://php.net/manual/en/function.natcasesort.php
     */
    public function sortNatural(bool $caseSensitive = false): Collection
    {
        $data = $this->items;

        if ($caseSensitive) {
            \natsort($data);
        } else {
            \natcasesort($data);
        }

        return new static($data);
    }

    /**
     * Sort collection data by one or multiple values.
     *
     * This method uses [[ArrayHelper::multisort()]] on the collection data.
     *
     * Note that keys will not be preserved by this method.
     *
     * The original collection will not be changed, a new collection with sorted data is returned.
     *
     * @param string|\Closure|array $key the key(s) to be sorted by. This refers to a key name of the sub-array
     * elements, a property name of the objects, or an anonymous function returning the values for comparison
     * purpose. The anonymous function signature should be: `function($item)`.
     * To sort by multiple keys, provide an array of keys here.
     * @param int|array $direction the sorting direction. It can be either `SORT_ASC` or `SORT_DESC`.
     * When sorting by multiple keys with different sorting directions, use an array of sorting directions.
     * @param int|array $sortFlag the PHP sort flag. Valid values include
     * `SORT_REGULAR`, `SORT_NUMERIC`, `SORT_STRING`, `SORT_LOCALE_STRING`, `SORT_NATURAL` and `SORT_FLAG_CASE`.
     * Please refer to the [PHP manual](http://php.net/manual/en/function.sort.php)
     * for more details. When sorting by multiple keys with different sort flags, use an array of sort flags.
     *
     * @return static a new collection containing the sorted items.
     * @throws InvalidArgumentException if the $direction or $sortFlag parameters do not have
     * correct number of elements as that of $key.
     * @see ArrayHelper::multisort()
     */
    public function sortBy($key, $direction = SORT_ASC, $sortFlag = SORT_REGULAR): Collection
    {
        $data = $this->items;

        ArrayHelper::multisort($data, $key, $direction, $sortFlag);

        return new static($data);
    }

    /**
     * Reverse the order of items.
     *
     * The original collection will not be changed, a new collection with items in reverse order is returned.
     * @return static a new collection containing the items in reverse order.
     */
    public function reverse(): Collection
    {
        return new static(\array_reverse($this->items, true));
    }

    /**
     * Return items without keys.
     * @return static a new collection containing the values of this collections data.
     */
    public function values(): Collection
    {
        return new static(\array_values($this->items));
    }

    /**
     * Return keys of all collection items.
     * @return static a new collection containing the keys of this collections data.
     */
    public function keys(): Collection
    {
        return new static(\array_keys($this->items));
    }

    /**
     * Flip keys and values of all collection items.
     * @return static a new collection containing the data of this collections flipped by key and value.
     */
    public function flip(): Collection
    {
        return new static(\array_flip($this->items));
    }

    /**
     * Merge two collections or this collection with an array.
     *
     * Data in this collection will be overwritten if non-integer keys exist in the merged collection.
     *
     * The original collection will not be changed, a new collection with items in reverse order is returned.
     *
     * @param iterable|Collection $collection the collection or array to merge with.
     *
     * @return static a new collection containing the merged data.
     */
    public function merge(iterable $collection): Collection
    {
        return new static(\array_merge($this->items, $collection));
    }

    /**
     * Convert collection data by selecting a new key and a new value for each item.
     *
     * Builds a map (key-value pairs) from a multidimensional array or an array of objects.
     * The `$from` and `$to` parameters specify the key names or property names to set up the map.
     *
     * The original collection will not be changed, a new collection with newly mapped data is returned.
     *
     * @param string|\Closure $from the field of the item to use as the key of the created map.
     * This can be a closure that returns such a value.
     * @param string|\Closure $to the field of the item to use as the value of the created map.
     * This can be a closure that returns such a value.
     *
     * @return static a new collection containing the mapped data.
     * @see ArrayHelper::map()
     */
    public function remap($from, $to): Collection
    {
        return new static(ArrayHelper::map($this->items, $from, $to));
    }

    /**
     * Assign a new key to each item in the collection.
     *
     * The original collection will not be changed, a new collection with newly mapped data is returned.
     *
     * @param string|\Closure $key the field of the item to use as the new key.
     * This can be a closure that returns such a value.
     *
     * @return static a new collection containing the newly index data.
     * @see ArrayHelper::map()
     */
    public function indexBy($key): Collection
    {
        return $this->remap($key, function ($model) {
            return $model;
        });
    }

    /**
     * Group items by a specified value.
     *
     * The original collection will not be changed, a new collection with grouped data is returned.
     *
     * @param string|\Closure $groupField the field of the item to use as the group value.
     * This can be a closure that returns such a value.
     * @param bool $preserveKeys whether to preserve item keys in the groups. Defaults to `true`.
     *
     * @return static a new collection containing the grouped data.
     * @see ArrayHelper::map()
     */
    public function groupBy($groupField, bool $preserveKeys = true): Collection
    {
        $result = [];

        if ($preserveKeys) {
            foreach ($this->items as $key => $element) {
                $result[ArrayHelper::getValue($element, $groupField)][$key] = $element;
            }
        } else {
            foreach ($this->items as $key => $element) {
                $result[ArrayHelper::getValue($element, $groupField)][] = $element;
            }
        }

        return new static($result);
    }

    /**
     * Check whether the collection contains a specific item.
     *
     * @param mixed|\Closure $item the item to search for. You may also pass a closure that returns a boolean.
     * The closure will be called on each item and in case it returns `true`, the item will be considered to
     * be found. In case a closure is passed, `$strict` parameter has no effect.
     * @param bool $strict whether comparison should be compared strict (`===`) or not (`==`).
     * Defaults to `false`.
     *
     * @return bool `true` if the collection contains at least one item that matches, `false` if not.
     */
    public function contains($item, bool $strict = false): bool
    {
        if ($item instanceof \Closure) {
            foreach ($this->items as $i) {
                if ($item($i)) {
                    return true;
                }
            }
        } else {
            return \in_array($item, $this->items, $strict);
        }

        return false;
    }

    /**
     * Remove a specific item from the collection.
     *
     * The original collection will not be changed, a new collection with modified data is returned.
     *
     * @param mixed|\Closure $item the item to search for. You may also pass a closure that returns a boolean.
     * The closure will be called on each item and in case it returns `true`, the item will be removed.
     * In case a closure is passed, `$strict` parameter has no effect.
     * @param bool $strict whether comparison should be compared strict (`===`) or not (`==`).
     * Defaults to `false`.
     *
     * @return static a new collection containing the filtered items.
     * @see filter()
     */
    public function remove($item, bool $strict = false): Collection
    {
        if ($item instanceof \Closure) {
            $callbackFilter = function ($i) use ($item) {
                return !$item($i);
            };
        } elseif ($strict) {
            $callbackFilter = function ($i) use ($item) {
                return $i !== $item;
            };
        } else {
            $callbackFilter = function ($i) use ($item) {
                return $i != $item;
            };
        }

        return $this->filter($callbackFilter);
    }

    /**
     * Replace a specific item in the collection with another one.
     *
     * The original collection will not be changed, a new collection with modified data is returned.
     *
     * @param mixed $item the item to search for.
     * @param mixed $replacement the replacement to insert instead of the item.
     * @param bool $strict whether comparison should be compared strict (`===`) or not (`==`).
     * Defaults to `false`.
     *
     * @return static a new collection containing the new set of items.
     * @see map()
     */
    public function replace($item, $replacement, bool $strict = false): Collection
    {
        return $this->map(function ($i) use ($item, $replacement, $strict) {
            if ($strict ? $i === $item : $i == $item) {
                return $replacement;
            }

            return $i;
        });
    }

    /**
     * Slice the set of elements by an offset and number of items to return.
     *
     * The original collection will not be changed, a new collection with the selected data is returned.
     *
     * @param int $offset starting offset for the slice.
     * @param int|null $limit the number of elements to return at maximum.
     * @param bool $preserveKeys whether to preserve item keys.
     *
     * @return static a new collection containing the new set of items.
     */
    public function slice($offset, $limit = null, bool $preserveKeys = true): Collection
    {
        return new static(\array_slice($this->items, $offset, $limit, $preserveKeys));
    }

    /**
     * Apply Pagination to the collection.
     *
     * This will return a portion of the data that maps the the page calculated by the pagination object.
     *
     * Usage example:
     *
     * ```php
     * $collection = new Collection($data);
     * $pagination = new Pagination([
     *     'totalCount' => $collection->count(),
     *     'pageSize' => 3,
     * ]);
     * // the current page will be determined from request parameters
     * $pageData = $collection->paginate($pagination)->getData());
     * ```
     *
     * The original collection will not be changed, a new collection with the selected data is returned.
     *
     * @param Pagination $pagination the pagination object to retrieve page information from.
     * @param bool $preserveKeys
     *
     * @return static a new collection containing the items for the current page.
     * @see Pagination
     */
    public function paginate(Pagination $pagination, bool $preserveKeys = false): Collection
    {
        $limit = $pagination->getLimit();

        return $this->slice($pagination->getOffset(), $limit > 0 ? $limit : null, $preserveKeys);
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     *
     * @return bool true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
