<?php

namespace skewer\components\import\field;

use skewer\components\catalog;

/**
 * Обработчик уникального поля (артикул).
 */
class Unique extends Prototype
{
    /** @var bool Создавать новые */
    protected $create = true;

    protected static $parameters = [
        'create' => [
            'name' => 'create',
            'title' => 'field_unique_create',
            'datatype' => 'i',
            'viewtype' => 'check',
            'default' => 1,
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function isUnique()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->values;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeExecute()
    {
        if ($this->values) {
            $this->values = array_shift($this->values);
            $this->config->setParam('unique_value', $this->values);

            $oGoodsRow = catalog\Api::getByField($this->fieldName, $this->values, $this->getCard());

            $this->config->setParam('new', false);
            if (!$oGoodsRow && $this->create) {
                $oGoodsRow = catalog\Api::createGoodsRow($this->getCard());
                $this->config->setParam('new', true);
            }

            if ($oGoodsRow) {
                $this->setGoodsRow($oGoodsRow);
            }
        }
    }
}
