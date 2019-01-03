<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Kartavik\Yii\Tests\Mock;

use Kartavik\Yii\Model\Collection;

/**
 * Class CustomerCollection
 * @package Kartavik\Yii\Tests\Mock
 */
class CustomerCollection extends Collection
{
    public function sumAge(): int
    {
        return $this->sum('age');
    }
}
