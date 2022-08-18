<?php

namespace skewer\components\seo;

use skewer\base\log\Logger;
use skewer\base\queue as QM;
use skewer\base\queue\Task;
use skewer\base\SysVar;
use skewer\components\search;
use skewer\components\search\models\SearchIndex;

/**
 * Задача на обновление поискового индекса.
 */
class SearchTask extends Task
{
    /** @var int Количество обработанных записей за одну итерацию */
    private $iCountByIteration = 0;

    /** @var int Количество ошибок(за весь процесс обновления, не за одну итерацию) */
    private $iCountError = 0;

    /** @var int Макс. количество записей, обрабатываемых за итерацию */
    private $iLimit = 50;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        SysVar::set('Search.updatedByIteration', 0);
        SysVar::set('Search.countError', 0);
    }

    /**
     * {@inheritdoc}
     */
    public function recovery()
    {
        SysVar::set('Search.updatedByIteration', 0);
        $this->iCountError = SysVar::get('Search.countError', 0);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /*
         * Делаем искуственные ограничения, yii валится на тестовом из-за логера
         */
        if (!$this->iLimit) {
            $this->setStatus(static::stInterapt);

            return false;
        }
        --$this->iLimit;
        $aRow = SearchIndex::find()->where(['status' => 0])->asArray()->one();

        //Получили поисковый класс для записи
        if (!$aRow) {
            $this->setStatus(static::stComplete);

            return false;
        }

        $oSearch = search\Api::getSearch($aRow['class_name']);

        if (!$oSearch) {
            return false;
        }

        try {
            //обновим запись в поиске
            $oSearch->updateByObjectId($aRow['object_id'], false);
        } catch (\Exception $e) {
            // В случае ошибки сохраняем запись пустой и обработанной
            $res = $oSearch->updateAsEmpty(true);

            Logger::error('[search_index_error]: Ошибка при обновлении поисковой записи: ' . "\n" . (string) $e . "\n");

            ++$this->iCountError;
        }

        ++$this->iCountByIteration;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function afterExecute()
    {
        SysVar::set('Search.updatedByIteration', $this->iCountByIteration);
        SysVar::set('Search.countError', $this->iCountError);
    }

    /**
     * Метод, вызываемый по завершении задачи.
     */
    public function complete()
    {
        /*
         * Цепляем задачу на сайтмап, если ее нет
         */
        QM\Api::addTask(SitemapTask::getConfig());
    }

    /**
     * Получить имя класса.
     *
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * Получить конфиг задачи.
     *
     * @return array
     */
    public static function getConfig()
    {
        return [
            'title' => 'search index update',
            'class' => self::className(),
            'priority' => QM\Task::priorityHigh,
        ];
    }
}
