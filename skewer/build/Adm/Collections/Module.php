<?php

namespace skewer\build\Adm\Collections;

use skewer\base\section\Parameters;
use skewer\base\ui;
use skewer\build\Adm;
use skewer\build\Catalog\Collections\Api;
use skewer\build\Design\Zones;
use skewer\build\Page\CatalogViewer\State\CollectionOnMain;
use skewer\components\auth\CurrentAdmin;
use skewer\helpers\Transliterate;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Класс модуля управления несколькими коллекциями в разделе.
 */
class Module extends Adm\ZonesEditor\Module
{
    /** Метод, выполняемый перед action методами */
    protected function preExecute()
    {
    }

    /** Состояние: Список коллекций раздела */
    protected function actionInit()
    {
        $this->title = \Yii::t('Collections', 'Collections.Adm.tab_name');
        $this->setPanelName(\Yii::t('Collections', 'state_list'));
        $this->aFiltredZones = ['content'];

        $aData = [];
        $iKey = 0;
        foreach ($this->getCurrentGroupsNames() as $sGroupName) {
            ++$iKey;
            $aData[] = [
                'group' => $sGroupName,
                'title' => Parameters::getValByName($this->sectionId(), $sGroupName, Zones\Api::layoutTitleName, true) ?: \Yii::t('Collections', 'title_row') . " №{$iKey}",
            ];
        }

        $this->render(new view\Index([
            'aData' => $aData,
            'bIsSystemMode' => CurrentAdmin::isSystemMode(),
        ]));
    }

    /** Состояние: Редактирование/добавление коллекции */
    protected function actionAddEditCollection()
    {
        $this->title = \Yii::t('Collections', 'Collections.Adm.tab_name');
        $this->setPanelName(\Yii::t('Collections', 'state_add_edit'));

        $sGroup = $this->getInDataVal('group');

        /** Параметры коллекции по умолчанию */
        $aData = [
            'group' => $sGroup,
            Parameters::object => 'CatalogViewer',
            Zones\Api::layoutParamName => 'content',
            'onMainCollection' => 1,
            'template' => 'slider',
        ];

        if ($sGroup) {
            $aDataGroup = Parameters::getList($this->sectionId())
                ->group($sGroup)
                ->index('name')
                ->rec()->asArray()->get();

            $aData = ArrayHelper::getColumn($aDataGroup, 'value') + $aData;
        }

        $this->render(new view\AddEditCollection([
            'sParamObj' => Parameters::object,
            'sLayoutParamName' => Zones\Api::layoutParamName,
            'sLayoutTitleName' => Zones\Api::layoutTitleName,
            'listTemplateMData' => ArrayHelper::getColumn(CollectionOnMain::$aTemplates, 'title'),
            'aCollectionsSections' => \skewer\components\ext\FormView::markUniqueValue(Api::getCollectionsSections()),
            'aData' => $aData,
        ]));
    }

    /** Действие: сохранение коллекции */
    protected function actionSaveCollection()
    {
        $aData = $this->getInData();

        if (!trim($aData[Zones\Api::layoutTitleName])) {
            throw new UserException(\Yii::t('Collections', 'error_title'));
        }
        // Генерация уникальной группы
        if (!$aData['group']) {
            $sGroup = $aAlias = mb_substr(Transliterate::generateAlias($aData[Zones\Api::layoutTitleName]), 0, 20);
            $i = 0;
            while (Parameters::getList($this->sectionId())->group($sGroup)->rec()->asArray()->get()) {
                $sGroup = $aAlias . '-' . ++$i;
            }
            $aData['group'] = $sGroup;
        }

        $sGroup = $aData['group'];
        unset($aData['group']);

        foreach ($aData as $sName => $sValue) {
            $oParam = Parameters::getByName($this->sectionId(), $sGroup, $sName, true, true);
            $oParam or $oParam = Parameters::createParam(['name' => $sName, 'group' => $sGroup]);
            if ($oParam->value == $sValue) {
                continue;
            }

            $oParam->value = $sValue;

            ($oParam->parent == $this->sectionId()) ? $oParam->save() : Parameters::copyToSection($oParam, $this->sectionId());
        }

        $this->actionInit();
    }

    /** Действие: удаление коллекции */
    protected function actionDeleteCollection()
    {
        if ($sGroup = $this->getInDataVal('group')) {
            // Удалить из всех зон вывода на странице
            foreach (Zones\Api::getZoneList($this->sectionId()) as $aZone) {
                Zones\Api::deleteLabel($sGroup, $aZone['id']);
            }

            // Удалить из параметров раздела
            Parameters::removeByGroup($sGroup, $this->sectionId());
        }

        $this->actionInit();
    }

    /** Получить массив имён групп с объектами коллекций для главной для текущего раздела */
    protected function getCurrentGroupsNames()
    {
        /** Все области страницы */
        $aZones = Zones\Api::getZoneList($this->sectionId());

        /** Список всех используемых групп в разделе */
        $aLabels = [];
        foreach ($aZones as &$paZone) {
            if (!$this->aFiltredZones or in_array($paZone['name'], $this->aFiltredZones)) {
                foreach (Zones\Api::getLabelList($paZone['id'], $this->sectionId()) as $aLabel) {
                    $aLabels[$aLabel['name']] = 1;
                }
            }
        }

        /** Параметры с объектом колекций */
        $aCollectionsObjParams = Parameters::getList($this->sectionId())
            ->name(Parameters::object)
            ->value('CatalogViewer')
            ->index('group')
            ->rec()->asArray()->get();

        // Отсортировать согласно следованию зон на странице, а отсутствующие группы добавить в конец
        $aCollectionsObjParams = array_intersect_key($aLabels, $aCollectionsObjParams) + array_diff_key($aCollectionsObjParams, $aLabels);

        /** Параметры коллекций с выводом на главную */
        $aCollectionsOnmainParams = Parameters::getList($this->sectionId())
            ->name('onMainCollection')
            ->valueNotEmpty()
            ->index('group')
            ->rec()->asArray()->get();

        // Оставить только параметры коллекций с выводом на главную страницу
        return array_keys(array_intersect_key($aCollectionsObjParams, $aCollectionsOnmainParams));
    }

    /**
     * Установка служебных данных.
     *
     * @param ui\state\BaseInterface $oIface
     */
    protected function setServiceData(ui\state\BaseInterface $oIface)
    {
        $oIface->setServiceData([
        ]);
    }
}
