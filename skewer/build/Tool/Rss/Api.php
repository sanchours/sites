<?php

namespace skewer\build\Tool\Rss;

use skewer\base\queue;
use skewer\base\section\models\TreeSection;
use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\base\SysVar;
use yii\base\ModelEvent;
use yii\db\AfterSaveEvent;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/**
 * Класс для построения файлов RSS рассылки.
 */
class Api
{
    /** @const Событие по сбору записей для rss ленты */
    const EVENT_GET_DATA = 'rss_get_data';

    /** @const Cобытие, запускающее перестроение Rss */
    const EVENT_REBUILD_RSS = 'rebuild_rss';

    /**
     * @const Количество записей для RSS, собираемое с модуля
     */
    const COUNT_RECORDS_PER_MODULE = 20;

    /**
     * @const Количество записей,
     * попадающих в Rss - ленту
     */
    const COUNT_RSS_RECORDS = 20;

    /**
     * @const Имя rss файла
     */
    const FILENAME_RSS = 'feed.xml';

    /**
     * Возвращает rss директорию.
     *
     * @return string
     */
    public static function getDirRss()
    {
        return WEBPATH . 'files/rss/';
    }

    /**
     * Вернет ссылку на rss - ленту.
     *
     * @return string
     */
    public static function getRssLink()
    {
        return '/files/rss/' . self::FILENAME_RSS;
    }

    /**
     * Ставит задачу на перестроение rss.
     *
     * @return bool
     */
    public static function rebuildRss()
    {
        return queue\Api::addTask(Task::getConfig());
    }

    /**
     * Вернет имя класса.
     *
     * @return mixed
     */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * Обработчик события TreeSection::EVENT_AFTER_UPDATE
     * При изменении атрибутов parent, alias, visible запускает перестроение rss.
     *
     * @param AfterSaveEvent $event
     */
    public static function updateSection(AfterSaveEvent $event)
    {
        /** @var TreeSection $oSection */
        $oSection = $event->sender;

        if (in_array($oSection->id, self::getSectionsIncludedInRss())) {
            if (!isset($event->changedAttributes['parent']) and
                !isset($event->changedAttributes['alias']) and
                !isset($event->changedAttributes['visible'])) {
                return;
            }

            self::rebuildRss();
        }
    }

    /**
     * Обработчик события TreeSection::EVENT_AFTER_DELETE
     * Запускает перестроение rss.
     *
     * @param ModelEvent $event
     */
    public static function removeSection(ModelEvent $event)
    {
        /** @var TreeSection $oSection */
        $oSection = $event->sender;

        if (in_array($oSection->id, self::getSectionsIncludedInRss())) {
            self::unlinkSection($oSection->id);
            self::rebuildRss();
        }
    }

    /**
     * Удаление раздела из списка разделов,используемых в rss.
     *
     * @param int $iSectionId
     */
    public static function unlinkSection($iSectionId)
    {
        if (!$iSectionId) {
            return;
        }

        $aSections = self::getSectionsIncludedInRss();
        $key = array_search($iSectionId, $aSections);
        if ($key !== false) {
            ArrayHelper::remove($aSections, $key);
            SysVar::set('Rss.sections', implode(',', $aSections));
        }
    }

    /**
     * Собирает записи c сущностей,
     * подписавшихся на событие self::EVENT_GET_DATA.
     *
     * @return array
     */
    public static function getRssContent()
    {
        $oEvent = new GetRowsEvent();
        \Yii::$app->trigger(self::EVENT_GET_DATA, $oEvent);

        if (!$oEvent->aRows) {
            return [];
        }

        // Выбираем из собранного контента Api::COUNT_RSS_RECORDS самых новых записей
        usort(
            $oEvent->aRows,
            static function ($a, $b) {
                if (strtotime($a->publication_date) == strtotime($b->publication_date)) {
                    return 0;
                }

                return (strtotime($a->publication_date) < strtotime($b->publication_date)) ? 1 : -1;
            }
        );

        return array_slice($oEvent->aRows, 0, Api::COUNT_RSS_RECORDS);
    }

    /**
     * Вернет разделы, содержащие контент
     * для rss ленты(новости/статьи).
     *
     * @return array
     */
    public static function getSection4Rss()
    {
        $aTemplateSection = Tree::getSubSections(\Yii::$app->sections->templates());
        $aTemplates = [];

        foreach ($aTemplateSection as $item) {
            $sMainModule = mb_strtolower(Parameters::getValByName($item->id, 'content', 'object'));

            if (($sMainModule === 'news') || ($sMainModule === 'articles')) {
                $aTemplates[] = $item->id;
            }
        }

        $aParams = Parameters::getList()->group('.')->name('template')->value($aTemplates)->asArray()->get();
        $aSectionsId = ArrayHelper::getColumn($aParams, 'parent');

        $aSections = TreeSection::find()
            ->where(['id' => $aSectionsId])
            ->asArray()
            ->all();

        if (!$aSections) {
            return [];
        }

        return ArrayHelper::map($aSections, 'id', static function ($row) {
            return $row['title'] . '(' . $row['id'] . ') ';
        });
    }

    /**
     * Получить разделы, используемые в rss.
     *
     * @return array
     */
    public static function getSectionsIncludedInRss()
    {
        $aSections = SysVar::get('Rss.sections', '');

        return StringHelper::explode($aSections, ',');
    }
} //class
