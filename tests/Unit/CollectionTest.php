<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Kartavik\Yii\Tests\Unit;

use Kartavik\Yii;
use yii\data\Pagination;

/**
 * Class CollectionTest
 * @package Kartavik\Yii\Tests
 */
class CollectionTest extends Yii\Tests\TestCase
{
    protected function getIteratorModels(): array
    {
        return [
            new Yii\Tests\Mock\Customer(['id' => 1]),
            new Yii\Tests\Mock\Customer(['id' => 2]),
            new Yii\Tests\Mock\Customer(['id' => 3]),
        ];
    }

    public function testIterator(): void
    {
        $collection = new Yii\Collection($this->getIteratorModels());
        $it = 0;
        foreach ($collection as $model) {
            $this->assertInstanceOf(Yii\Tests\Mock\Customer::class, $model);
            $this->assertEquals($it + 1, $model->id);
            ++$it;
        }
        $this->assertEquals(3, $it);

        $collection = new Yii\Collection($this->getIteratorModels());
        $it = 0;
        foreach ($collection as $key => $model) {
            $this->assertInstanceOf(Yii\Tests\Mock\Customer::class, $model);
            $this->assertEquals($it, $key);
            $this->assertEquals($it + 1, $model->id);
            ++$it;
        }
        $this->assertEquals(3, $it);
    }

    public function testArrayAccessRead(): void
    {
        $models = [
            new Yii\Tests\Mock\Customer(['id' => 1]),
            new Yii\Tests\Mock\Customer(['id' => 2]),
            new Yii\Tests\Mock\Customer(['id' => 3]),
        ];
        $collection = new Yii\Collection($models);
        $this->assertTrue(isset($collection[0]));
        $this->assertInstanceOf(Yii\Tests\Mock\Customer::class, $collection[0]);
        $this->assertEquals(1, $collection[0]->id);
        $this->assertTrue(isset($collection[1]));
        $this->assertInstanceOf(Yii\Tests\Mock\Customer::class, $collection[1]);
        $this->assertEquals(2, $collection[1]->id);
        $this->assertTrue(isset($collection[2]));
        $this->assertInstanceOf(Yii\Tests\Mock\Customer::class, $collection[2]);
        $this->assertEquals(3, $collection[2]->id);
        $this->assertFalse(isset($collection[3]));

        $models = [
            'one' => new Yii\Tests\Mock\Customer(['id' => 1]),
            'two' => new Yii\Tests\Mock\Customer(['id' => 2]),
            'three' => new Yii\Tests\Mock\Customer(['id' => 3]),
        ];
        $collection = new Yii\Collection($models);
        $this->assertTrue(isset($collection['one']));
        $this->assertInstanceOf(Yii\Tests\Mock\Customer::class, $collection['one']);
        $this->assertEquals(1, $collection['one']->id);
        $this->assertTrue(isset($collection['two']));
        $this->assertInstanceOf(Yii\Tests\Mock\Customer::class, $collection['two']);
        $this->assertEquals(2, $collection['two']->id);
        $this->assertTrue(isset($collection['three']));
        $this->assertInstanceOf(Yii\Tests\Mock\Customer::class, $collection['three']);
        $this->assertEquals(3, $collection['three']->id);
        $this->assertFalse(isset($collection['four']));
    }

    public function testArrayAccessWrite(): void
    {
        $models = [
            'one' => new Yii\Tests\Mock\Customer(['id' => 1]),
            'two' => new Yii\Tests\Mock\Customer(['id' => 2]),
            'three' => new Yii\Tests\Mock\Customer(['id' => 3]),
        ];
        $collection = new Yii\Collection($models);
        $collection['three'] = 'test';

        $this->assertEquals($collection['three'], 'test');
    }

    public function testArrayAccessWriteWithOffset(): void
    {
        $models = [
            'one' => new Yii\Tests\Mock\Customer(['id' => 1]),
            'two' => new Yii\Tests\Mock\Customer(['id' => 2]),
            'three' => new Yii\Tests\Mock\Customer(['id' => 3]),
        ];
        $collection = new Yii\Collection($models);
        $collection[] = 'test';

        $this->assertEquals($collection[3], 'test');
    }

    public function testArrayAccessUnset(): void
    {
        $models = [
            'one' => new Yii\Tests\Mock\Customer(['id' => 1]),
            'two' => new Yii\Tests\Mock\Customer(['id' => 2]),
            'three' => new Yii\Tests\Mock\Customer(['id' => 3]),
        ];
        $collection = new Yii\Collection($models);
        unset($collection['two']);

        $this->assertArrayNotHasKey('two', $collection);
    }

    public function testCountable(): void
    {
        $collection = new Yii\Collection([]);
        $this->assertEquals(0, count($collection));
        $this->assertEquals(0, $collection->count());

        $models = [
            new Yii\Tests\Mock\Customer(['id' => 1]),
            new Yii\Tests\Mock\Customer(['id' => 2]),
            new Yii\Tests\Mock\Customer(['id' => 3]),
        ];
        $collection = new Yii\Collection($models);
        $this->assertEquals(3, count($collection));
        $this->assertEquals(3, $collection->count());
    }

    public function testIsEmpty(): void
    {
        $collection = new Yii\Collection([]);
        $this->assertTrue($collection->isEmpty());

        $models = [
            new Yii\Tests\Mock\Customer(['id' => 1]),
            new Yii\Tests\Mock\Customer(['id' => 2]),
            new Yii\Tests\Mock\Customer(['id' => 3]),
        ];
        $collection = new Yii\Collection($models);
        $this->assertFalse($collection->isEmpty());
    }

    public function testMap(): void
    {
        $models = [
            new Yii\Tests\Mock\Customer(['id' => 1]),
            new Yii\Tests\Mock\Customer(['id' => 2]),
            new Yii\Tests\Mock\Customer(['id' => 3]),
        ];
        $collection = new Yii\Collection($models);
        $this->assertEquals([1, 2, 3], $collection->map(function ($model) {
            return $model->id;
        })->getItems());
    }

    public function testFilter(): void
    {
        $models = [
            new Yii\Tests\Mock\Customer(['id' => 1]),
            new Yii\Tests\Mock\Customer(['id' => 2]),
            new Yii\Tests\Mock\Customer(['id' => 3]),
        ];
        $collection = new Yii\Collection($models);
        $this->assertEquals([1 => 2], $collection->filter(function ($model) {
            return $model->id == 2;
        })->map(function ($model) {
            return $model->id;
        })->getItems());

        $collection = new Yii\Collection($models);
        $this->assertEquals([1 => 2, 2 => 3], $collection->filter(function ($model, $key) {
            return $model->id == 2 || $key == 2;
        })->map(function ($model) {
            return $model->id;
        })->getItems());
    }

    public function testReduce(): void
    {
        $models = [
            new Yii\Tests\Mock\Customer(['id' => 1]),
            new Yii\Tests\Mock\Customer(['id' => 2]),
            new Yii\Tests\Mock\Customer(['id' => 3]),
        ];
        $collection = new Yii\Collection($models);
        $this->assertEquals(12, $collection->reduce(function ($carry, $model) {
            return $model->id + $carry;
        }, 6));
    }

    public function testSum(): void
    {
        $collection = new Yii\Collection([]);
        $this->assertEquals(0, $collection->sum('id'));
        $this->assertEquals(0, $collection->sum('age'));

        $models = [
            new Yii\Tests\Mock\Customer(['id' => 1, 'age' => -2]),
            new Yii\Tests\Mock\Customer(['id' => 2, 'age' => 2]),
            new Yii\Tests\Mock\Customer(['id' => 3, 'age' => 42]),
        ];
        $collection = new Yii\Collection($models);
        $this->assertEquals(6, $collection->sum('id'));
        $this->assertEquals(42, $collection->sum('age'));

        $collection = new Yii\Collection([-2, 1, 3]);
        $this->assertEquals(2, $collection->sum());
    }

    public function testMin(): void
    {
        $collection = new Yii\Collection([]);
        $this->assertEquals(0, $collection->min('id'));
        $this->assertEquals(0, $collection->min('age'));

        $models = [
            new Yii\Tests\Mock\Customer(['id' => 1, 'age' => -2]),
            new Yii\Tests\Mock\Customer(['id' => 2, 'age' => 2]),
            new Yii\Tests\Mock\Customer(['id' => 3, 'age' => 42]),
        ];
        $collection = new Yii\Collection($models);
        $this->assertEquals(1, $collection->min('id'));
        $this->assertEquals(-2, $collection->min('age'));

        $collection = new Yii\Collection([-2, 1, 3]);
        $this->assertEquals(-2, $collection->min());
    }

    public function testMax(): void
    {
        $collection = new Yii\Collection([]);
        $this->assertEquals(0, $collection->max('id'));
        $this->assertEquals(0, $collection->max('age'));

        $models = [
            new Yii\Tests\Mock\Customer(['id' => 1, 'age' => -2]),
            new Yii\Tests\Mock\Customer(['id' => 2, 'age' => 2]),
            new Yii\Tests\Mock\Customer(['id' => 3, 'age' => 42]),
        ];
        $collection = new Yii\Collection($models);
        $this->assertEquals(3, $collection->max('id'));
        $this->assertEquals(42, $collection->max('age'));

        $collection = new Yii\Collection([-2, 1, 3]);
        $this->assertEquals(3, $collection->max());
    }

    public function testKeys(): void
    {
        $data = [
            'a',
            'b' => 'c',
            1 => 'test',
        ];
        $collection = new Yii\Collection($data);
        $this->assertSame([0, 'b', 1], $collection->keys()->getItems());
    }

    public function testValues(): void
    {
        $data = [
            'a',
            'b' => 'c',
            1 => 'test',
        ];
        $collection = new Yii\Collection($data);
        $this->assertSame(['a', 'c', 'test'], $collection->values()->getItems());
    }

    public function testFlip(): void
    {
        $data = [
            'a',
            'b' => 'c',
            1 => 'test',
        ];
        $collection = new Yii\Collection($data);
        $this->assertSame(['a' => 0, 'c' => 'b', 'test' => 1], $collection->flip()->getItems());
    }

    public function testReverse(): void
    {
        $data = [
            'a',
            'b' => 'c',
            1 => 'test',
        ];
        $collection = new Yii\Collection($data);
        $this->assertSame([1 => 'test', 'b' => 'c', 0 => 'a'], $collection->reverse()->getItems());
    }

    public function testMerge(): void
    {
        $data1 = ['a', 'b', 'c'];
        $data2 = [1, 2, 3];
        $collection1 = new Yii\Collection($data1);
        $collection2 = new Yii\Collection($data2);
        $this->assertEquals(['a', 'b', 'c', 1, 2, 3], $collection1->merge($collection2)->getItems());
        $this->assertEquals([1, 2, 3, 'a', 'b', 'c'], $collection2->merge($collection1)->getItems());
        $this->assertEquals(['a', 'b', 'c', 1, 2, 3], $collection1->merge($data2)->getItems());
        $this->assertEquals([1, 2, 3, 'a', 'b', 'c'], $collection2->merge($data1)->getItems());
    }

    public function testReMap(): void
    {
        $models = [
            new Yii\Tests\Mock\Customer(['id' => 1, 'age' => -2]),
            new Yii\Tests\Mock\Customer(['id' => 2, 'age' => 2]),
            new Yii\Tests\Mock\Customer(['id' => 3, 'age' => 42]),
        ];
        $collection = new Yii\Collection($models);
        $this->assertEquals([1 => -2, 2 => 2, 3 => 42], $collection->remap('id', 'age')->getItems());
        $this->assertEquals(['1-2' => -1, '22' => 4, '342' => 45], $collection->remap(
            function ($model) {
                return $model->id . $model->age;
            },
            function ($model) {
                return $model->id + $model->age;
            }
        )->getItems());
    }

    public function testIndexBy(): void
    {
        $models = [
            new Yii\Tests\Mock\Customer(['id' => 1, 'age' => -2]),
            new Yii\Tests\Mock\Customer(['id' => 2, 'age' => 2]),
            new Yii\Tests\Mock\Customer(['id' => 3, 'age' => 42]),
        ];
        $expected = [
            1 => $models[0],
            2 => $models[1],
            3 => $models[2],
        ];
        $collection = new Yii\Collection($models);
        $this->assertEquals($expected, $collection->indexBy('id')->getItems());
    }

    public function testGroupBy(): void
    {
        $models = [
            1 => new Yii\Tests\Mock\Customer(['id' => 1, 'age' => 2]),
            2 => new Yii\Tests\Mock\Customer(['id' => 2, 'age' => 2]),
            3 => new Yii\Tests\Mock\Customer(['id' => 3, 'age' => 42]),
        ];
        $expectedByIdWithKeys = [
            1 => [
                1 => $models[1],
            ],
            2 => [
                2 => $models[2],
            ],
            3 => [
                3 => $models[3],
            ],
        ];
        $expectedByIdWithoutKeys = [
            1 => [
                $models[1],
            ],
            2 => [
                $models[2],
            ],
            3 => [
                $models[3],
            ],
        ];
        $expectedByAgeWithKeys = [
            2 => [
                1 => $models[1],
                2 => $models[2],
            ],
            42 => [
                3 => $models[3],
            ],
        ];
        $expectedByAgeWithoutKeys = [
            2 => [
                $models[1],
                $models[2],
            ],
            42 => [
                $models[3],
            ],
        ];
        $collection = new Yii\Collection($models);
        $this->assertEquals($expectedByIdWithKeys, $collection->groupBy('id')->getItems());
        $this->assertEquals($expectedByIdWithoutKeys, $collection->groupBy('id', false)->getItems());
        $this->assertEquals($expectedByAgeWithKeys, $collection->groupBy('age')->getItems());
        $this->assertEquals($expectedByAgeWithoutKeys, $collection->groupBy('age', false)->getItems());
    }

    public function testContains(): void
    {
        $data = [1, 2, 3, 4, 5, 6];
        $collection = new Yii\Collection($data);
        $this->assertTrue($collection->contains(1, false));
        $this->assertTrue($collection->contains('1', false));
        $this->assertTrue($collection->contains(1, true));
        $this->assertFalse($collection->contains('1', true));

        $this->assertFalse($collection->contains(8, false));
        $this->assertFalse($collection->contains('8', false));
        $this->assertFalse($collection->contains(8, true));
        $this->assertFalse($collection->contains('8', true));

        $this->assertFalse($collection->contains(function ($item) {
            return $item > 6;
        }, false));
        $this->assertTrue($collection->contains(function ($item) {
            return $item > 5;
        }, false));
        $this->assertFalse($collection->contains(function ($item) {
            return $item > 6;
        }, true));
        $this->assertTrue($collection->contains(function ($item) {
            return $item > 5;
        }, true));
    }

    public function testRemove(): void
    {
        $collection = new Yii\Collection([1, 2, 3, 4, 5, 6]);
        $this->assertEquals([1, 2, 4, 5, 6], $collection->remove(3, false)->values()->getItems());
        $this->assertEquals([1, 2, 4, 5, 6], $collection->remove('3', false)->values()->getItems());
        $this->assertEquals([1, 2, 4, 5, 6], $collection->remove(3, true)->values()->getItems());
        $this->assertEquals([1, 2, 3, 4, 5, 6], $collection->remove('3', true)->getItems());
        $this->assertEquals([1, 2, 3, 4, 5, 6], $collection->remove(7, false)->getItems());
        $this->assertEquals([1, 2, 3, 4, 5, 6], $collection->remove('7', false)->getItems());
        $this->assertEquals([1, 2, 3, 4, 5, 6], $collection->remove(7, true)->getItems());
        $this->assertEquals([1, 2, 3, 4, 5, 6], $collection->remove('7', true)->getItems());

        $this->assertEquals([1, 2, 3], $collection->remove(function ($i) {
            return $i > 3;
        }, false)->getItems());
        $this->assertEquals([1, 2, 3], $collection->remove(function ($i) {
            return $i > 3;
        }, true)->getItems());
    }

    public function testReplace(): void
    {
        $collection = new Yii\Collection([1, 2, 3, 4, 5, 6]);
        $this->assertEquals([1, 2, 9, 4, 5, 6], $collection->replace(3, 9, false)->getItems());
        $this->assertEquals([1, 2, 9, 4, 5, 6], $collection->replace('3', 9, false)->getItems());
        $this->assertEquals([1, 2, 9, 4, 5, 6], $collection->replace(3, 9, true)->getItems());
        $this->assertEquals([1, 2, 3, 4, 5, 6], $collection->replace('3', 9, true)->getItems());
        $this->assertEquals([1, 2, 3, 4, 5, 6], $collection->replace(7, 9, false)->getItems());
        $this->assertEquals([1, 2, 3, 4, 5, 6], $collection->replace('7', 9, false)->getItems());
        $this->assertEquals([1, 2, 3, 4, 5, 6], $collection->replace(7, 9, true)->getItems());
        $this->assertEquals([1, 2, 3, 4, 5, 6], $collection->replace('7', 9, true)->getItems());

        $collection = new Yii\Collection([1, 2, 3, 4, 3, 6]);
        $this->assertEquals([1, 2, 9, 4, 9, 6], $collection->replace(3, 9, false)->getItems());
        $this->assertEquals([1, 2, 9, 4, 9, 6], $collection->replace('3', 9, false)->getItems());
        $this->assertEquals([1, 2, 9, 4, 9, 6], $collection->replace(3, 9, true)->getItems());
        $this->assertEquals([1, 2, 3, 4, 3, 6], $collection->replace('3', 9, true)->getItems());
        $this->assertEquals([1, 2, 3, 4, 3, 6], $collection->replace(7, 9, false)->getItems());
        $this->assertEquals([1, 2, 3, 4, 3, 6], $collection->replace('7', 9, false)->getItems());
        $this->assertEquals([1, 2, 3, 4, 3, 6], $collection->replace(7, 9, true)->getItems());
        $this->assertEquals([1, 2, 3, 4, 3, 6], $collection->replace('7', 9, true)->getItems());
    }

    public function testSort(): void
    {
        $data = [4, 6, 5, 8, 11, 1];
        $collection = new Yii\Collection($data);
        $this->assertEquals([1, 4, 5, 6, 8, 11], $collection->sort(SORT_ASC, SORT_REGULAR)->values()->getItems());
        $this->assertEquals([1, 11, 4, 5, 6, 8], $collection->sort(SORT_ASC, SORT_STRING)->values()->getItems());
        $this->assertEquals([11, 8, 6, 5, 4, 1], $collection->sort(SORT_DESC, SORT_REGULAR)->values()->getItems());
        $this->assertEquals([8, 6, 5, 4, 11, 1], $collection->sort(SORT_DESC, SORT_STRING)->values()->getItems());
    }

    public function testSortByKey(): void
    {
        $data = [5 => 4, 44 => 55, 55 => 44, 4 => 5];
        $collection = new Yii\Collection($data);
        $this->assertEquals(
            [4 => 5, 5 => 4, 44 => 55, 55 => 44],
            $collection->sortByKey(SORT_ASC, SORT_REGULAR)->getItems()
        );
        $this->assertEquals(
            [4 => 5, 44 => 55, 5 => 4, 55 => 44],
            $collection->sortByKey(SORT_ASC, SORT_STRING)->getItems()
        );
        $this->assertEquals(
            [55 => 44, 44 => 55, 5 => 4, 4 => 5],
            $collection->sortByKey(SORT_DESC, SORT_REGULAR)->getItems()
        );
        $this->assertEquals(
            [55 => 44, 5 => 4, 44 => 55, 4 => 5],
            $collection->sortByKey(SORT_DESC, SORT_STRING)->getItems()
        );
    }

    public function testSortNatural(): void
    {
        $data = ['100.', '1.', '11.', '2.'];
        $collection = new Yii\Collection($data);
        $this->assertEquals(['1.', '2.', '11.', '100.'], $collection->sortNatural(false)->values()->getItems());
        $this->assertEquals(['1.', '2.', '11.', '100.'], $collection->sortNatural(true)->values()->getItems());

        $data = ['anti', 'Auto', 'Zett', 'beta'];
        $collection = new Yii\Collection($data);
        $this->assertEquals(['anti', 'Auto', 'beta', 'Zett'], $collection->sortNatural(false)->values()->getItems());
        $this->assertEquals(['Auto', 'Zett', 'anti', 'beta'], $collection->sortNatural(true)->values()->getItems());
    }

    public function testSortBy(): void
    {
        $models = [
            2 => new Yii\Tests\Mock\Customer(['id' => 2, 'age' => 42]),
            1 => new Yii\Tests\Mock\Customer(['id' => 1, 'age' => 2]),
            3 => new Yii\Tests\Mock\Customer(['id' => 3, 'age' => 2]),
        ];
        $collection = new Yii\Collection($models);
        $this->assertSame([
            $models[1],
            $models[2],
            $models[3],
        ], $collection->sortBy('id')->getItems());
        $this->assertSame([
            $models[3],
            $models[2],
            $models[1],
        ], $collection->sortBy('id', SORT_DESC)->getItems());
        $this->assertSame([
            $models[1],
            $models[3],
            $models[2],
        ], $collection->sortBy(['age', 'id'])->getItems());
        $this->assertSame([
            $models[3],
            $models[1],
            $models[2],
        ], $collection->sortBy(['age', 'id'], [SORT_ASC, SORT_DESC])->getItems());
    }

    public function testSlice(): void
    {
        $data = [1, 2, 3, 4, 5];
        $collection = new Yii\Collection($data);
        $this->assertEquals([3 => 4, 4 => 5], $collection->slice(3)->getItems());
        $this->assertEquals([3 => 4], $collection->slice(3, 1)->getItems());
        $this->assertEquals([1, 2], $collection->slice(0, 2)->getItems());
        $this->assertEquals([1 => 2, 2 => 3], $collection->slice(1, 2)->getItems());
    }

    public function testPaginate(): void
    {
        $data = [1, 2, 3, 4, 5];
        $collection = new Yii\Collection($data);
        $pagination = new Pagination([
            'totalCount' => $collection->count(),
            'pageSize' => 3,
        ]);
        $pagination->page = 0;
        $this->assertEquals([1, 2, 3], $collection->paginate($pagination)->getItems());
        $pagination->page = 1;
        $this->assertEquals([4, 5], $collection->paginate($pagination)->getItems());

        $pagination = new Pagination([
            'totalCount' => $collection->count(),
            'pageSizeLimit' => false,
            'pageSize' => -1,
        ]);
        $pagination->page = 0;
        $this->assertEquals([1, 2, 3, 4, 5], $collection->paginate($pagination)->getItems());
    }
}
