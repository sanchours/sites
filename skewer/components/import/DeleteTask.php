<?php

namespace skewer\components\import;

use skewer\base\queue;
use skewer\components\catalog;

/**
 * Задача на удаление товаров
 * Class DeleteTask.
 */
class DeleteTask extends queue\Task
{
    /** @var string Имя поля, по которому идет удаление */
    private $fieldName = '';

    /** @var string Имя карточки */
    private $card = '';

    /** @var int Родительская задача */
    private $parentTask = 0;

    /** @var int Шаблон импорта */
    private $tpl = 0;

    /** @var \skewer\base\orm\state\StateSelect Запросник */
    private $query;

    /** @var Logger Логер */
    private $logger;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $aArgs = func_get_args();

        $this->fieldName = (isset($aArgs[0]['field_name'])) ? $aArgs[0]['field_name'] : '';
        $this->card = (isset($aArgs[0]['card'])) ? $aArgs[0]['card'] : '';
        $this->parentTask = (isset($aArgs[0]['parentTask'])) ? $aArgs[0]['parentTask'] : '';
        $this->tpl = (isset($aArgs[0]['tpl'])) ? $aArgs[0]['tpl'] : '';

        if (!$this->fieldName) {
            $this->setStatus(static::stError);
        }

        $this->logger = new Logger($this->parentTask, $this->tpl);
    }

    /**
     * {@inheritdoc}
     */
    public function recovery()
    {
        $aArgs = func_get_args();

        $this->fieldName = (isset($aArgs[0]['field_name'])) ? $aArgs[0]['field_name'] : '';
        $this->card = (isset($aArgs[0]['card'])) ? $aArgs[0]['card'] : '';
        $this->parentTask = (isset($aArgs[0]['parentTask'])) ? $aArgs[0]['parentTask'] : '';
        $this->tpl = (isset($aArgs[0]['tpl'])) ? $aArgs[0]['tpl'] : '';

        if (!$this->fieldName) {
            $this->setStatus(static::stError);
        }

        $this->logger = new Logger($this->parentTask, $this->tpl);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeExecute()
    {
        $this->logger->setSaved(['delete_list']);

        if (!$this->card) {
            $this->query = catalog\Api::selectAllFromField($this->fieldName);
        } else {
            $this->query = catalog\Api::selectFromField($this->fieldName, $this->card);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->query) {
            $this->setStatus(static::stError);

            return false;
        }

        /** получаем запись */
        $oRow = $this->query->each();

        if (!$oRow) {
            $this->setStatus(static::stComplete);

            return true;
        }

        /* Удаляем */
        if (catalog\Api::deleteGoods($oRow['id'])) {
            $this->logger->incParam('delete');
            if (isset($oRow['title'])) {
                $this->logger->setListParam('delete_list', $oRow['title']);
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function reservation()
    {
        $this->setParams(['field_name' => $this->fieldName, 'card' => $this->card,
            'parentTask' => $this->parentTask, 'tpl' => $this->tpl, ]);
        $this->logger->save();
    }

    /**
     * {@inheritdoc}
     */
    public function complete()
    {
        $this->logger->setParam('finish', date('Y-m-d H:i:s'));
        $this->logger->save();
    }

    /**
     * {@inheritdoc}
     */
    public function error()
    {
        $this->logger->save();
    }
}
