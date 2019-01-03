<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Kartavik\Yii\Model;

use yii\base;
use yii\db;
use yii\helpers\Json;

/**
 * Collection
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @author Roman Varkuta <roman.varkuta@gmail.com>
 * @since 1.0
 */
class Collection extends \Kartavik\Yii\Collection
{
    /**
     * @var db\ActiveQuery|null the query that returned this collection.
     * May be`null` if the collection has not been created by a query.
     */
    public $query;

    /**
     * @var array|db\BaseActiveRecord[]
     */
    private $models;

    /**
     * Collection constructor.
     *
     * @param array $models
     * @param array $config
     */
    public function __construct($models = [], $config = [])
    {
        $this->models = $models;
        parent::__construct([], $config);
    }

    /**
     * Lazy evaluation of models, if this collection has been created from a query.
     */
    public function getData()
    {
        $this->ensureModels();
        return (new Collection($this->models))->getData();
    }

    private function queryAll()
    {
        if ($this->query === null) {
            throw new base\InvalidCallException('This collection was not created from a query.');
        }
        return $this->query->all();
    }

    private function ensureModels()
    {
        if ($this->models === null) {
            $this->models = $this->queryAll();
        }
    }

    /**
     * @return array|db\BaseActiveRecord[]|db\ActiveRecordInterface[]|base\Arrayable[] models contained in this
     *     collection.
     */
    public function getModels()
    {
        return $this->getData();
    }

    // TODO relational operations like link() and unlink() sync()
    // https://github.com/yiisoft/yii2/pull/12304#issuecomment-242339800
    // https://github.com/yiisoft/yii2/issues/10806#issuecomment-242346294

    // TODO addToRelation() by checking if query is a relation
    // https://github.com/yiisoft/yii2/issues/10806#issuecomment-241505294


    // https://github.com/yiisoft/yii2/issues/12743
    public function findWith($with)
    {
        if (!$this->query) {
            throw new base\InvalidCallException(
                'This collection was not created from a query, so findWith() is not possible.'
            );
        }
        $this->ensureModels();
        $this->query->findWith($with, $this->models);
        return $this;
    }

    /**
     * https://github.com/yiisoft/yii2/issues/13921
     *
     * TODO add transaction support
     *
     * @throws db\Exception
     * @throws db\StaleObjectException
     */
    public function deleteAll()
    {
        $this->ensureModels();
        foreach ($this->models as $model) {
            $model->delete();
        }
    }

    public function scenario($scenario)
    {
        $this->ensureModels();
        foreach ($this->models as $model) {
            $model->scenario = $scenario;
        }
        return $this;
    }

    /**
     * https://github.com/yiisoft/yii2/issues/13921
     *
     * TODO add transaction support
     *
     * @param array $attributes
     * @param bool $safeOnly
     * @param bool $runValidation
     *
     * @return Collection
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function updateAll($attributes, $safeOnly = true, $runValidation = true)
    {
        $this->ensureModels();
        foreach ($this->models as $model) {
            $model->setAttributes($attributes, $safeOnly);
            $model->update($runValidation, array_keys($attributes));
        }
        return $this;
    }

    /**
     * @param array $attributes
     * @param bool $safeOnly
     * @param bool $runValidation
     *
     * @return $this
     */
    public function insertAll($attributes, $safeOnly = true, $runValidation = true)
    {
        $this->ensureModels();
        foreach ($this->models as $model) {
            $model->setAttributes($attributes, $safeOnly);
            $model->insert($runValidation, array_keys($attributes));
        }
        // TODO could be a batch insert
        return $this;
    }

    public function fillAll($attributes, $safeOnly = true)
    {
        $this->ensureModels();
        foreach ($this->models as $model) {
            $model->setAttributes($attributes, $safeOnly);
        }
        return $this;
    }

    public function saveAll($runValidation = true, $attributeNames = null)
    {
        $this->ensureModels();
        foreach ($this->models as $model) {
            $model->save($runValidation, $attributeNames);
        }
        return $this;
    }

    /**
     * https://github.com/yiisoft/yii2/issues/10806#issuecomment-242119472
     *
     * @return bool
     */
    public function validateAll()
    {
        $this->ensureModels();
        $success = true;
        foreach ($this->models as $model) {
            if (!$model->validate()) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * @param array $fields
     * @param array $expand
     * @param bool $recursive
     *
     * @return Collection|static
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return $this->map(function ($model) use ($fields, $expand, $recursive) {
            /** @var $model base\Arrayable */
            return $model->toArray($fields, $expand, $recursive);
        });
    }

    /**
     * Encodes the collected models into a JSON string.
     *
     * @param int $options the encoding options. For more details please refer to
     * <http://www.php.net/manual/en/function.json-encode.php>. Default is `JSON_UNESCAPED_SLASHES |
     *     JSON_UNESCAPED_UNICODE`.
     *
     * @return string the encoding result.
     */
    public function toJson($options = 320)
    {
        return Json::encode($this->toArray()->models, $options);
    }
}
