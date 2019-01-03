<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Kartavik\Yii\Tests\Unit;

use Kartavik\Yii;
use yii\db\ActiveQuery;

/**
 * Class ModelCollectionTest
 * @package Kartavik\Yii\Tests
 */
class ModelCollectionTest extends Yii\Tests\TestCase
{
    public function testCollect(): void
    {
        $this->assertInstanceOf(Yii\Collection::class, Yii\Tests\Mock\Customer::find()->collect());
        $this->assertInstanceOf(ActiveQuery::class, Yii\Tests\Mock\Customer::find()->collect()->query);
        $this->assertEquals(0, Yii\Tests\Mock\Customer::find()->collect()->count());
    }

    /**
     * @depends testCollect
     */
    public function testCollectCustomClass(): void
    {
        $this->assertInstanceOf(
            Yii\Tests\Mock\CustomerCollection::class,
            Yii\Tests\Mock\Customer::find()->collect(Yii\Tests\Mock\CustomerCollection::class)
        );
    }
}
