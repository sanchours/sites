<?php

namespace skewer\build\Design\Zones;

use skewer\base\site\Site;
use skewer\build\Cms;
use yii\helpers\ArrayHelper;

/**
 * Модуль для вывода панели с редакторм параметров
 * Class Module.
 */
class Module extends Cms\Frame\ModulePrototype
{
    /**
     * Состояние. Выбор корневого набора разделов.
     */
    protected function actionInit()
    {
        // команда инициализации
        $this->setCmd('init');

        $this->addLibClass('ZoneTemplates');
        $this->addLibClass('ZoneSelector');
        $this->addLibClass('ZoneLabels');

        $this->loadTplList();
    }

    /**
     * Перезагружает набор шаблонов
     * Загружает набор шаблонов/разделов -> выбирает активный.
     */
    protected function actionReloadTplList()
    {
        // Текущий url
        $sShowUrl = $this->getStr('showUrl', '/');
        $sShowUrl = str_replace(Site::httpDomain(), '', $sShowUrl);

        // шаблоны и разделы
        $aPages = Api::getSectionsWithOverridenZone($sShowUrl);
        $this->setData('tplList', $aPages);

        // id шаблона для подсветки
        $iTplId = $this->getInt('tplId');
        $aPages = ArrayHelper::index($aPages, 'id');

        // Активная страница
        $iActivePageId = !isset($aPages[$iTplId]) ? Api::getSectionIdByPath($sShowUrl) : $iTplId;

        $this->highlightTpl($iActivePageId);
        $this->actionSelectTemplate($iActivePageId);
    }

    /**
     * Подсвечивает заданный шаблон.
     *
     * @param $iTplId
     */
    private function highlightTpl($iTplId)
    {
        $this->setData('selectTpl', $iTplId);
    }

    /**
     * Загружает набор шаблонов.
     */
    private function loadTplList()
    {
        // установка набора шаблонов
        $this->setData('tplList', Api::getSectionsWithOverridenZone($this->getStr('showUrl', '/')));
    }

    /**
     * При выборе шаблона.
     *
     * @param int $iTplId - идентификатор шаблона
     */
    protected function actionSelectTemplate($iTplId = 0)
    {
        if (!$iTplId) {
            $iTplId = $this->getInt('tplId');
        }

        // выбрать набор параметров шаблона
        $aParams = Api::getZoneList($iTplId);

        // отдать набор зон
        $this->setData('zoneList', $aParams);

        // список меток очистить
        $this->setData('labelList', []);
        $this->setData('labelAddList', []);
    }

    /**
     * Удаление зоны.
     */
    protected function actionDeleteZone()
    {
        // идентификатор зоны
        $iZoneId = $this->getInt('zoneId');
        // идентификатор шаблона
        $iTplId = $this->getInt('tplId');

        // удаление зоны для шаблона
        $iRes = Api::deleteZone($iZoneId, $iTplId);

        // выдать сообщение
        if ($iRes) {
            $this->addMessage('Значения зоны сброшены');
        } else {
            $this->addError('Зону удалить нельзя');
        }

        // загрузить набор зон шаблона
        $this->actionReloadTplList();

        // установить флаг перезагрузки
        $this->setData('reload', true);
    }

    /**
     * Выбор зоны.
     *
     * @param int $iZoneId перекрывающий идентификатор
     * @param int $iTplId
     */
    protected function actionSelectZone($iZoneId = null, $iTplId = 0)
    {
        if (!$iTplId) {
            $iTplId = $this->getInt('tplId');
        }
        $iInZoneId = $this->getInt('zoneId');

        if ($iZoneId === null) {
            $iZoneId = $iInZoneId;
        } elseif ($iZoneId !== $iInZoneId) {
            // выбрать зону в интерфейсе, если не совпадают
            $this->setData('selectZone', $iZoneId);
        }

        // отдать текущий список меток
        $this->setData('labelList', Api::getLabelList($iZoneId, $iTplId));

        // отдать список доступных меток
        $this->setData('labelAddList', Api::getAddLabelList($iZoneId, $iTplId));
    }

    /**
     * Выбирает зону по имени.
     */
    protected function actionSelectZoneByName()
    {
        // вычислить номер шаблона
        $iTplId = Api::getTplIdByPath($this->getStr('showUrl', '/'));
        $sZoneName = $this->getStr('zoneName');

        // подсветить его
        $this->highlightTpl($iTplId);

        // вычислить id зоны по имени для шаблона
        $iZoneId = Api::getZoneIdByName($sZoneName, $iTplId);

        // отдать набор зон
        $this->setData('zoneList', Api::getZoneList($iTplId));

        // выбрать зону в шаблоне
        $this->actionSelectZone($iZoneId, $iTplId);
    }

    /**
     * Отдает id собственного для раздела id зоны
     * если нужно зона создается для данного раздела.
     *
     * @return int
     */
    protected function getOwnZoneId()
    {
        $iTplId = $this->getInt('tplId');
        $iZoneId = $this->getInt('zoneId');

        // проверить принадлежность
        $iOutZoneId = Api::getZoneForTpl($iZoneId, $iTplId);

        // если чужая
        if ($iOutZoneId !== $iZoneId) {
            $iZoneId = $iOutZoneId;
            $this->addMessage('Данные зоны скопированы для текущего шаблона');
        }

        return $iZoneId;
    }

    /**
     * Отображает набор зон и содержимое выбранной.
     *
     * @param $iZoneId
     */
    protected function showAll($iZoneId)
    {
        $this->actionSelectTemplate();
        $this->actionSelectZone($iZoneId);
    }

    /**
     * Сортировка набора меток.
     */
    protected function actionSaveLabels()
    {
        // идентификатор собственной зоны
        $iZoneId = $iZoneId = $this->getOwnZoneId();

        // данные для сортировки
        $aLabels = $this->get('items');

        $iTpl = $this->get('tplId');

        // сортировка
        Api::saveLabels($aLabels, $iZoneId, $iTpl);

        // загружаем данные в интерфейс
        $this->showAll($iZoneId);

        // установить флаг перезагрузки
        $this->setData('reload', true);
    }
}
