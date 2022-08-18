<?php

namespace skewer\build\Adm\ZonesEditor;

use skewer\base\section\Parameters;
use skewer\base\site\Layer;
use skewer\base\ui;
use skewer\build\Adm;
use skewer\build\Adm\ParamSettings;
use skewer\build\Design\Zones;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/**
 * Класс модуля редактирования областей вывода модулей раздела
 * Может использоваться как самостоятельный модуль или внутри других модулей слоя Adm.
 */
class Module extends Adm\Tree\ModulePrototype
{
    /** @var int id зоны */
    public $zoneId = 0;

    /** @var array Фильтр доступных для редактирования зон */
    protected $aFiltredZones = ['content'];

    private $aPossibleZones = [
        'base' => [
              'content',
              'right',
              'left',
        ],
        'one' => [
              'content',
        ],
    ];

    private $dictKeys = [
        'notParameterDragRowOrDragHover',
        'GroupDragAndDropNotAllowed',
        'DraggableFieldsMustBeFromTheSameGroup'
    ];

    /** Метод, выполняемый перед action методами */
    protected function preExecute()
    {
        $this->addInitParam('dict', $this->parseLangVars($this->dictKeys));
    }

    /** Первичное состояние в режиме работы отдельного модуля */
    protected function actionInit()
    {
        $this->actionEditLayers();
    }

    /** Состояние: Редактирование областей вывода модулей на странице
     * @param array $aOpenedGroups
     */
    protected function actionEditLayers(array $aOpenedGroups = [])
    {

        /** Все области страницы */
        $aZones = Zones\Api::getZoneList($this->sectionId());

        /** Список всех групп в разделе, сгруппированных по зонам */
        $aLabels = [];
        foreach ($aZones as $iKey => $aZone) {
            if (!$this->aFiltredZones or in_array($aZone['name'], $this->aFiltredZones)) {
                $aLabels[$iKey] = Zones\Api::getListAllLabels($aZone['id'], $this->sectionId(), $this->sectionId());
            }
        }

        /** Данные для передачи в шаблон */
        $aData = [];

        foreach ($aLabels as $iKey => &$paLabels) {
            foreach ($paLabels as &$paLabel) {
                $aData[] = [
                    'inherited' => $paLabel['inherited'],
                    'useInZone' => $paLabel['useInZone'],
                    'name' => $paLabel['name'],
                    'title' => \Yii::tSingleString($paLabel['title']),
                    'zone_id' => $aZones[$iKey]['id'],
                    'groupTitle' => "[{$iKey}] " . $aZones[$iKey]['title'], // Цифра вначале нужна для сохранения сортировки, иначе js сортирует по алфавиту
                ];
            }
        }

        $this->render(new view\EditLayers([
            'bManyLabelsCount' => (count($aLabels) > 1),
            'aOpenedGroups' => $aOpenedGroups,
            'aCurrentGroupsNames' => $this->getCurrentGroupsNames(),
            'aData' => $aData,
        ]));
    }

    /** Действие: Активация/деактивация модуля */
    public function actionToogleActivity()
    {
        // Пришедшие данные
        $aData = $this->get('data');

        if (!isset($aData['useInZone'])) {
            throw new UserException(\Yii::t('ZonesEditor', 'not_transferred_data'));
        }
        // Флаг указывающий на то, что метка используется в зоне
        $bUseInZone = (bool) $aData['useInZone'];

        // id зоны
        $iZoneId = $aData['zone_id'];

        // Флаг указывающий на то, что зона унаследованна
        $bZoneInherited = true;

        if (!($oZoneParam = Parameters::getById($iZoneId))) {
            throw new UserException('Зона не найдена');
        }
        if ($oZoneParam->parent === $this->sectionId()) {
            $bZoneInherited = false;
        }

        if ($bZoneInherited) {
            // копируем зону в текущий раздел
            $oNewZone = Parameters::copyToSection($oZoneParam, $this->sectionId());
            $iZoneId = $oNewZone->id;
        }

        if ($bUseInZone) {
            Zones\Api::deleteLabel($aData['name'], $iZoneId);
        } else {
            Zones\Api::addLabel($aData['name'], $iZoneId, $this->sectionId());
        }

        $this->actionInit();
    }

    /** Действие: Удаление метки из зоны, вместе с группой параметров */
    protected function actionDeleteOrCopy()
    {
        // Пришедшие данные
        $aData = $this->get('data');

        // Группа параметров метки переопределена?
        $bInherited = (bool) $aData['inherited'];

        // Параметр, указывающий на того, кем модуль был ранее добавлен
        $sParam = Parameters::getValByName($this->sectionId(), $aData['name'], Zones\Api::OWNER, true);

        if (!$sParam || ($sParam && ($sParam !== Zones\Api::USER_OWNER))) {
            throw new UserException(\Yii::t('ZonesEditor', 'error_system_denied'));
        }
        // имя модуля метки
        $sModuleName = Parameters::getValByName($this->sectionId(), $aData['name'], Parameters::object, true);

        // ищем спец.класс для модуля
        $sParamSettingsClass = '';
        if ($sModuleName && \Yii::$app->register->moduleExists($sModuleName, Layer::PAGE)) {
            $sParamSettingsClass = \Yii::$app->register->getModuleConfig($sModuleName, Layer::PAGE)->getVal('param_settings');
        }

        if (!$sParamSettingsClass || !class_exists($sParamSettingsClass)) {
            throw new UserException(sprintf(\Yii::t('ZonesEditor', 'error_no_module_class'), ParamSettings\Prototype::className()));
        }
        /** @var ParamSettings\Prototype $oParamSettings */
        $oParamSettings = new $sParamSettingsClass();
        $oParamSettings->setGroupName($aData['name']);
        $oParamSettings->setLabelTitle($aData['title']);
        $oParamSettings->setParent($this->sectionId());

        if ($bInherited) {
            $oParamSettings->copy();
        } else {
            // Удалить модуль
            $oParamSettings->delete();
            //Удалить метку из зоны
            Zones\Api::deleteLabel($aData['name'], $aData['zone_id']);
        }

        $this->actionInit();
    }

    /** Состояние: Добавление нового модуля */
    protected function actionAddingLabel()
    {
        if ($aPost = $this->getInData()) {
            $aData = [];

            $iZoneId = (!empty($aPost['zone_id'])) ? $aPost['zone_id'] : $this->zoneId;
            $this->zoneId = $iZoneId;

            $aLabels = Zones\Api::getAddLabelList($iZoneId, $this->sectionId());

            foreach ($aLabels as &$paLabel) {
                $aData[] = [
                    'name' => $paLabel['name'],
                    'title' => $paLabel['title'],
                    'nameAfter' => $aPost['name'],
                    'zone_id' => $iZoneId,
                    'own' => $paLabel['own'],
                ];
            }

            $this->render(new view\AddingLabel([
                'aCurrentGroupsNames' => $this->getCurrentGroupsNames(),
                'aData' => $aData,
            ]));
        }
    }

    /** Состояние: Настройка добавляемого модуля */
    protected function actionEditModule()
    {
        $aZones = ArrayHelper::index(Zones\Api::getZoneList($this->sectionId()), 'name');

        // Зоны доступные для редактирования
        $aAvailable4EditZones = [];

        foreach ($aZones as $sNameZone => $aParamsZone) {
            if (in_array($sNameZone, $this->aFiltredZones)) {
                $aAvailable4EditZones[$aParamsZone['id']] = $aParamsZone['title'];

                if ($sNameZone == 'content') {
                    $this->zoneId = $aParamsZone['id'];
                }
            }
        }

        $this->render(new view\EditModule([
            'aInstallableModules' => ParamSettings\Api::getInstallableModules(),
        ]));
    }

    /**
     * Действие: Установка модуля на страницу
     * Установка заключается в добавлении группы параметров модуля на тек.страницу.
     *
     * @throws UserException
     */
    protected function actionInstallModule()
    {
        $aData = $this->getInData();

        if ($aData) {
            if (!$this->zoneId) {
                throw new UserException(\Yii::t('ZonesEditor', 'error_no_id'));
            }
            if (!$aData['title']) {
                throw new UserException(\Yii::t('ZonesEditor', 'error_no_module_name'));
            }
            $aConfig = StringHelper::explode($aData['paramSettingsClass'], ':');

            $sParamSettingsClass = ArrayHelper::getValue($aConfig, '0', '');
            $sSubType = ArrayHelper::getValue($aConfig, '1', '');

            if (!$sSubType) {
                throw new UserException(\Yii::t('ZonesEditor', 'error_no_module_type'));
            }
            if ($sParamSettingsClass) {
                /** @var ParamSettings\Prototype $oParamSettings */
                $oParamSettings = new $sParamSettingsClass();

                $oParamSettings->setGroupName($aData['name']);
                $oParamSettings->setLabelTitle($aData['title']);
                $oParamSettings->setParent($this->sectionId());

                $oParamSettings->install($sSubType);
                Zones\Api::addLabel($oParamSettings->getGroupName(), $this->zoneId, $this->sectionId());
            }

            $this->actionInit();
        }
    }

    /** Действие: Добавление нового модуля */
    protected function actionAddLabel()
    {
        if ($aPost = $this->getInData()) {
            $this->sortingLabels($aPost['name'], $aPost['nameAfter'], $aPost['zone_id']);

            $this->actionEditLayers([]);
        }
    }

    /** Действие: Сортировка/перемещение модулей зон */
    protected function actionSortLabels()
    {
        $aData = $this->getInData();
        $aDropData = $this->get('dropData');
        $sPosition = $this->get('position');

        if ($aData and $aDropData and $sPosition) {
            self::sortingLabels($aData['name'], $aDropData['name'], $aData['zone_id'], $aDropData['zone_id'], $sPosition);

            $this->actionEditLayers([$aData['groupTitle'], $aDropData['groupTitle']]);
        }
    }

    /**
     * Сортировка/перемещение модуля.
     *
     * @param string $sNameFrom Имя перемещаемого модуля
     * @param string $sNameTo Имя модуля относительно которого выполняется перемещение
     * @param int $iZoneIdFrom Id зоны перемещаемого модуля
     * @param bool|int $iZoneIdTo Id зоны в которую происходит перемещение. Если false, то = $iZoneIdFrom
     * @param string $sPosition Направление сортировки
     *
     * @throws UserException
     */
    private function sortingLabels($sNameFrom, $sNameTo, $iZoneIdFrom, $iZoneIdTo = false, $sPosition = 'after')
    {
        // Сортировку не применяем для не установленных в зону меток
        if (!Zones\Api::getActivityLabel($sNameFrom, $iZoneIdFrom, $this->sectionId())) {
            return;
        }

        if ($iZoneIdTo === false) {
            $iZoneIdTo = $iZoneIdFrom;
        }

        /** Перетаскивание между разными группами? */
        $bBetweenGroups = ($iZoneIdFrom != $iZoneIdTo);

        // Проверка возможности перемещения модуля в другую группу
        if (!($bSuccess = !$bBetweenGroups)) {
            foreach (Zones\Api::getAddLabelList($iZoneIdTo, $this->sectionId()) as $aAvailableLabel) {
                if ($bSuccess = ($aAvailableLabel['name'] == $sNameFrom)) {
                    break;
                }
            }
        }

        if (!$bSuccess) {
            throw new UserException(\Yii::t('ZonesEditor', 'error_group_moving'));
        }
        $aLabelsFrom = ArrayHelper::getColumn(Zones\Api::getLabelList($iZoneIdFrom, $this->sectionId()), 'name');

        if ($bBetweenGroups) {
            $aLabelsTo = ArrayHelper::getColumn(Zones\Api::getLabelList($iZoneIdTo, $this->sectionId()), 'name');
        } else {
            $aLabelsTo = &$aLabelsFrom;
        }

        $iKey = array_search($sNameFrom, $aLabelsFrom);
        if ($iKey !== false) {
            array_splice($aLabelsFrom, $iKey, 1);
        } // array_splice после удаления элементов переиндексирует массив. Здесь это нужно

        $iKey = array_search($sNameTo, $aLabelsTo);
        if (($iKey !== false) or !$aLabelsTo) {
            ($sPosition == 'before') ? array_splice($aLabelsTo, $iKey, 0, $sNameFrom) : array_splice($aLabelsTo, $iKey + 1, 0, $sNameFrom);

            Zones\Api::saveLabels($aLabelsFrom, $iZoneIdFrom, $this->sectionId());
            $bBetweenGroups and Zones\Api::saveLabels($aLabelsTo, $iZoneIdTo, $this->sectionId());
        } else {
            // Если не указан объект относительно которого выполняется перемещение, то добавляем в самый конец
            $aLabelsTo[] = $sNameFrom;
            Zones\Api::saveLabels($aLabelsTo, $iZoneIdTo, $this->sectionId());
        }
    }

    /** Получение массива имён меток для визуального выделения в списке. Актуально при использовании этого модуля в специализированных модулях */
    protected function getCurrentGroupsNames()
    {
        return [];
    }

    /**
     * Установка служебных данных.
     *
     * @param ui\state\BaseInterface $oIface
     */
    protected function setServiceData(ui\state\BaseInterface $oIface)
    {
        $oIface->setServiceData([
            'zoneId' => $this->zoneId,
        ]);
    }

    /**
     * Форма для добавления нового слоя.
     */
    protected function actionAddZoneForm()
    {
        $aPossibleZones = array_combine(
            array_keys($this->aPossibleZones),
            array_keys($this->aPossibleZones)
        );

        $aData = [
            'name' => 'new_container',
            'type' => reset($aPossibleZones),
        ];

        $this->render(new view\AddZone([
            'aTypes' => $aPossibleZones,
            'aData' => $aData,
        ]));
    }

    /**
     * Добавляет новый слой.
     */
    protected function actionAddZone()
    {
        $sZone = $this->getInDataVal('name');
        $sType = $this->getInDataVal('type');

        if (!isset($this->aPossibleZones[$sType])) {
            throw new UserException("Zone type [{$sType}] not found");
        }
        $aList = $this->aPossibleZones[$sType];

        $oParam = Parameters::getByName(
            $this->sectionId(),
            Zones\Api::layoutGroupName,
            Zones\Api::layoutList,
            true
        );

        if ($oParam) {
            Parameters::setParams(
                $this->sectionId(),
                Zones\Api::layoutGroupName,
                Zones\Api::layoutList,
                $oParam->value == '{show_val}' ? '{show_val}' : $oParam->value . ',' . $sZone,
                $oParam->show_val . ',' . $sZone,
                $oParam->title,
                0
            );
        }

        // контейнеры
        foreach ($aList as $sName) {
            Parameters::addParam([
                'name' => $sZone . '\\' . $sName,
                'title' => $sZone . '.' . $sName,
                'value' => '',
                'group' => Zones\Api::layoutGroupName,
                'parent' => $this->sectionId(),
                'access_level' => 0,
                'show_val' => '',
            ]);
        }

        // шаблон
        Parameters::addParam([
            'name' => $sZone . '\\content_tpl',
            'title' => 'Шаблон слоя ' . $sZone,
            'value' => $sType,
            'group' => Zones\Api::layoutGroupName,
            'parent' => $this->sectionId(),
            'access_level' => 0,
            'show_val' => $sType,
        ]);

        $this->actionInit();
    }
}
