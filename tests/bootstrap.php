<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

if (file_exists(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env')) {
    $dotEnv = new \Dotenv\Dotenv(dirname(__DIR__));
    $dotEnv->load();
}

\Yii::setAlias('@Kartavik/Yii/', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src');
\Yii::setAlias('@Kartavik/Yii/Tests', __DIR__);

Yii::setAlias('@runtime', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'runtime');
\Yii::setAlias('@configFile', __DIR__ . DIRECTORY_SEPARATOR . 'config.php');
