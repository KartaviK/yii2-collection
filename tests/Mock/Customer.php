<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Kartavik\Yii\Tests\Mock;

use yii\db\ActiveRecord;
use Kartavik\Yii\Collection\Behavior;

/**
 * Customer Model
 * @package Kartavik\Yii\Tests\Mock
 *
 * @property int $id
 * @property string $name
 * @property int $age
 */
class Customer extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customers';
    }

    /**
     * {@inheritdoc}
     * @return \yii\db\ActiveQuery|Behavior
     */
    public static function find()
    {
        $query = parent::find();
        $query->attachBehavior('collection', Behavior::class);

        return $query;
    }
}
