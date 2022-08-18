<?php

namespace skewer\build\Tool\YandexExport;

use skewer\base\queue\ar\Schedule;
use skewer\base\SysVar;
use skewer\base\ui;
use skewer\build\Adm;
use skewer\build\Tool;
use skewer\build\Tool\YandexExport;
use skewer\components\catalog;

class Module extends Adm\Order\Module implements Tool\LeftList\ModuleInterface
{
    private $iExportLimit = 50;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        Tool\LeftList\ModulePrototype::updateLanguage();
        parent::init();
    }

    public function getName()
    {
        return $this->getModuleName();
    }

    /**
     * Действие: Обновление файла выгрузки.
     */
    protected function actionRunExport()
    {
        $this->runTaskWithReboot(YandexExport\Task::getConfig(), 'runExport');

        $this->actionInit();
    }

    public function actionSettings()
    {
        $this->render(new YandexExport\view\Settings([
            'aValue' => [
                'shopName' => htmlspecialchars_decode(SysVar::getSafe('YandexExport.shopName', '')),
                'companyName' => htmlspecialchars_decode(SysVar::getSafe('YandexExport.companyName', '')),
                'localDeliveryCost' => SysVar::getSafe('YandexExport.localDeliveryCost', ''),
            ],
        ]));
    }

    public function actionSaveSettings()
    {
        $data = $this->getInData();
        $sShopName = htmlspecialchars($data['shopName']);
        $sCompanyName = htmlspecialchars($data['companyName']);
        $sLocalDeliveryCost = $data['localDeliveryCost'];

        SysVar::set('YandexExport.shopName', $sShopName);
        SysVar::set('YandexExport.companyName', $sCompanyName);
        SysVar::set('YandexExport.localDeliveryCost', $sLocalDeliveryCost);

        $this->actionInit();
    }

    public function actionUtils()
    {
        $aTree = Api::getSections();

        $group = catalog\Card::getGroupByName('yandex');
        $fields = $group->getFields();

        $aEditableFields = [];
        // собираем чекбоксы

        $aFieldNameTitle = [];
        foreach ($fields as $field) {
            if ($field->group == $group->id && $field->editor == 'check') {
                $aEditableFields[] = $field->name;

                $aFieldNameTitle[$field->name] = $field->title;
                foreach ($aTree as $k => $item) {
                    $aTree[$k][$field->name] = $field->def_value;

                    // Убрать не каталожные разделы из настроек
                    if (!$item['isCatalogSection']) {
                        unset($aTree[$k]);
                    }
                }
            }
        }

        $this->render(new YandexExport\view\Utils([
            'aFieldNameTitle' => $aFieldNameTitle,
            'aTree' => $aTree,
            'aEditableFields' => $aEditableFields,
        ]));

        return psComplete;
    }

    /**
     * Метод сохраняет набор настроек для товаров выбранного раздела
     * Сам список галочек не сохраняется, только изменяются данные товаров.
     *
     * @throws \Exception
     * @throws catalog\Exception
     */
    public function actionSaveParam()
    {
        $aData = $this->getInData();

        if (!$aData) {
            $aParams = $this->get('params');
            if (isset($aParams[0]['data'])) {
                $aData = $aParams[0]['data'];
            }
        }

        if (!isset($aData['id'])) {
            return;
        }

        $sectionId = $aData['id'];

        // оставляем только метки от яндекс каталога, удаляем все лишнее
        unset($aData['id'], $aData['title'], $aData['parent']);

        $this->addMessage(\Yii::t('yandexExport', 'goods_changed', [Api::setDataForSectionGoods($sectionId, $aData)]));

        $aData['id'] = $sectionId;
    }

    public function actionSaveCheck()
    {
        return psComplete;
    }

    public function actionInit()
    {
        $sRunExportTitle = (!is_file(WEBPATH . YandexExport\Task::sFilePath)) ?
            \Yii::t('yandexExport', 'create') :
            \Yii::t('yandexExport', 'refresh');

        $sReportText = SysVar::get('Yandex.report');
        $sLogs = SysVar::get('Yandex.log');

        $sHeadText = '';
        if ($sReportText && is_file(WEBPATH . YandexExport\Task::sFilePath)) {
            $sHeadText = $sReportText . $sLogs;
        } else {
            $sHeadText = 'Выгрузка не создана' . $sLogs;
        }

        $this->render(new YandexExport\view\Init([
            'sRunExportTitle' => $sRunExportTitle,
            'sHeadText' => $sHeadText,
        ]));

        return psComplete;
    }

    /** Состояние: "Настройка задания" */
    protected function actionShowTask()
    {
        $aTaskConfig = \skewer\build\Tool\YandexExport\Task::getConfig();

        $sCommand = json_encode([
            'class' => $aTaskConfig['class'],
            'parameters' => $aTaskConfig['parameters'],
        ]);

        $oTask = Schedule::findOne(['command' => $sCommand]);

        $this->render(new YandexExport\view\ShowTask([
            'aData' => $oTask ? $oTask->attributes : Tool\Schedule\Api::getBlankSettingTime(),
        ]));
    }

    /**
     * Действие: Сохранение задачи в планировщике.
     */
    protected function actionSaveTask()
    {
        $aData = $this->getInData();

        $aSettingsSchedule = \skewer\build\Tool\YandexExport\Task::getConfig();

        $aSettingsSchedule['command'] = json_encode(['class' => $aSettingsSchedule['class'], 'parameters' => $aSettingsSchedule['parameters']]);

        if (!$oSchedule = Schedule::findOne($aData['id'])) {
            $oSchedule = new Schedule();
            unset($aData['id']);
        }

        $oSchedule->setAttributes($aData + $aSettingsSchedule);

        if (!$oSchedule->save()) {
            throw new ui\ARSaveException($oSchedule);
        }
        $this->actionInit();
    }
}
