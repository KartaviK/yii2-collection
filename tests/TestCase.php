<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Kartavik\Yii\Tests;

use yii\phpunit;
use yii\helpers;

/**
 * Class TestCase
 * @package Kartavik\Yii\Tests
 */
abstract class TestCase extends phpunit\TestCase
{
    public function globalFixtures(): array
    {
        $fixtures = [
            [
                'class' => phpunit\MigrateFixture::class,
                'migrationNamespaces' => [
                    'Kartavik\\Yii\\Tests\\Migrations',
                ],
            ]
        ];
        return helpers\ArrayHelper::merge(parent::globalFixtures(), $fixtures);
    }
}
