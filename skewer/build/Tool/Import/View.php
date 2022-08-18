<?php

namespace skewer\build\Tool\Import;

use skewer\base\queue\Task;
use skewer\base\SysVar;
use skewer\base\ui\builder\FormBuilder;
use skewer\components\auth\CurrentAdmin;
use skewer\components\catalog;
use skewer\components\import\Api;
use skewer\components\import\ar\ImportTemplateRow;
use skewer\components\import\Config;
use yii\helpers\ArrayHelper;

/**
 * Класс для построений форм настройки.
 */
class View
{
    /**
     * Дополнение формы полями для нужного провайдера данных.
     *
     * @param FormBuilder $oForm
     * @param ImportTemplateRow $oTemplate
     *
     * @throws \Exception
     */
    public static function getProviderForm(FormBuilder &$oForm, ImportTemplateRow $oTemplate)
    {
        $oConfig = new Config($oTemplate);

        /* Если удаленный источник и нет локального файла - скачаем для примера */
        if ($oConfig->getParam('type') == Api::Type_Url && (!$oConfig->getParam('file') || !file_exists($oConfig->getParam('file')))) {
            $oConfig->setParam('file', Api::uploadFile($oConfig->getParam('source')));
            //  и запомним для будущих примеров
            $oTemplate->settings = json_encode($oConfig->getData());
            $oTemplate->save();
        }

        $oProvider = Api::getProvider($oConfig);

        $aParameters = $oProvider->getParameters();

        $aData = $oConfig->getData();

        /* Создаем поля для редактирования параметров */
        $oForm->field('id', 'ID', 'hide');
        foreach ($aParameters as $key => $aVal) {
            switch ($aVal['viewtype']) {
                case 'select':
                    $aList = [];
                    if (isset($aVal['method'])) {
                        $sMethodName = $aVal['method'];
                        $aList = $oProvider->{$sMethodName}();
                    }
                    $oForm->fieldSelect($key, \Yii::t('import', $aVal['title']), $aList, [], false);
                    break;

                default:
                    $oForm->field($key, \Yii::t('import', $aVal['title']), $aVal['viewtype'], $aVal['params'] ?? []);
                    break;
            }

            /* Значение полей по умолчанию */
            if (!isset($aData[$key])) {
                $aData[$key] = $aVal['default'];
            }
        }

        /** Пример данных из файла */
        $sExapmles = $oProvider->getExample();
        if ($sExapmles) {
            $oForm->field('example', \Yii::t('import', 'example'), 'show', ['labelAlign' => 'top']);
            $aData['example'] = $sExapmles;
        }

        $oForm->setValue($aData);
    }

    /**
     * Дополнение формы связями полей.
     *
     * @param FormBuilder $oForm
     * @param ImportTemplateRow $oTemplate
     *
     * @throws \Exception
     */
    public static function getFieldsForm(FormBuilder &$oForm, ImportTemplateRow $oTemplate)
    {
        $oConfig = new Config($oTemplate);

        /* Если удаленный источник и нет локального файла - скачаем для примера */
        if ($oConfig->getParam('type') == Api::Type_Url && (!$oConfig->getParam('file') || !file_exists($oConfig->getParam('file')))) {
            $oConfig->setParam('file', Api::uploadFile($oConfig->getParam('source')));
            //  и запомним для будущих примеров
            $oTemplate->settings = json_encode($oConfig->getData());
            $oTemplate->save();
        }

        $oProvider = Api::getProvider($oConfig);

        $aData = [];
        $aData['id'] = $oTemplate->id;
        $aFields = $oConfig->getParam('fields');
        if (is_array($aFields)) {
            foreach ($aFields as $aVal) {
                if ($aVal['type']) {
                    /* чтобы не путать незаданное поле с полем, которому соответствует 0 */
                    if ($aVal['importFields'] !== '') {
                        $aData['field_' . $aVal['name']] = $aVal['importFields'];
                    }

                    $aData['type_' . $aVal['name']] = $aVal['type'];
                }
            }
        }

        /* Создаем поля для редактирования параметров */
        $oForm->field('id', 'ID', 'hide');

        /** Пример данных из файла */
        $sExapmles = $oProvider->getExample();
        if ($sExapmles) {
            $oForm->field('example', \Yii::t('import', 'example'), 'show', ['labelAlign' => 'top']);
            $aData['example'] = $sExapmles;
        }

        /** Соответствие полей */
        $aInfoRow = $oProvider->getInfoRow();
        if ($aInfoRow) {
            $aFields = Api::getFieldList($oTemplate->card);

            /** Убираем сео поля */
            $aSeoFields = catalog\Api::getSeoFields();
            $aFields = array_diff_key($aFields, ArrayHelper::index($aSeoFields, static function ($a) { return $a; }));

            unset($aFields['id']);

            /** Хак на раздел */
            $aFields = array_merge(['section' => \Yii::t('import', 'section')], $aFields);

            $aHiddenFields = \skewer\build\Catalog\CardEditor\Api::getHiddenFields();

            $j = 0;
            foreach ($aFields as $i => $sValue) {
                if (array_search($sValue, $aHiddenFields) === false) {
                    ++$j;
                    $sGroup = (string) ($j);

                    $oForm->field('show_' . $i, \Yii::t('import', 'field'), 'show', ['groupTitle' => $sGroup, 'groupType' => FormBuilder::GROUP_TYPE_COLLAPSIBLE]);
                    $aData['show_' . $i] = $sValue . ' (' . $i . ')';

                    $oForm->fieldMultiSelect('field_' . $i, \Yii::t('import', 'field_type_card'), $aInfoRow, [], ['forceSelection' => false, 'groupTitle' => $sGroup, 'groupType' => FormBuilder::GROUP_TYPE_COLLAPSIBLE]);

                    $oForm->fieldSelect('type_' . $i, \Yii::t('import', 'field_type'), Api::getFieldTypeList(), ['forceSelection' => false, 'groupTitle' => $sGroup, 'groupType' => FormBuilder::GROUP_TYPE_COLLAPSIBLE], false);
                }
            }
        }

        $oForm->setValue($aData);
    }

    /**
     * Дополнение формы настройками полей.
     *
     * @param FormBuilder $oForm
     * @param ImportTemplateRow $oTemplate
     *
     * @throws \Exception
     */
    public static function getFieldsSettingsForm(FormBuilder &$oForm, ImportTemplateRow $oTemplate)
    {
        // массив с названиями полей и параметрами, которые следует вывести
        $aFieldsAdm = ['price'];

        $oConfig = new Config($oTemplate);

        $aData = [];
        $aData['id'] = $oTemplate->id;

        /* Создаем поля для редактирования параметров */
        $oForm->field('id', 'ID', 'hide');

        $aCardFields = Api::getFieldList($oTemplate->card);

        /** Убираем сео поля */
        $aSeoFields = catalog\Api::getSeoFields();
        $aCardFields = array_diff_key($aCardFields, ArrayHelper::index($aSeoFields, static function ($a) { return $a; }));

        /** Хак на раздел */
        $aCardFields = array_merge(['section' => \Yii::t('import', 'section')], $aCardFields);

        $aFields = $oConfig->getParam('fields');
        if (is_array($aFields)) {
            foreach ($aFields as $aVal) {
                // делаем проверку на пользователя и на вхождение поля в массив заданных значений
                if ((!CurrentAdmin::isSystemMode()) and !(in_array($aVal['name'], $aFieldsAdm))) {
                    continue;
                }
                if (!isset($aVal['type']) or !$aVal['type'] or !isset($aCardFields[$aVal['name']])) {
                    continue;
                }

                $sClassName = 'skewer\\components\\import\\field\\' . $aVal['type'];
                if (!class_exists($sClassName)) {
                    continue;
                }

                /** @noinspection PhpUndefinedMethodInspection */
                $aParams = $sClassName::getParameters();
                if (!$aParams) {
                    continue;
                }

                $sGroup = $aCardFields[$aVal['name']];

                foreach ($aParams as $k => $value) {
                    if ($value['viewtype'] == 'select') {
                        $aList = [];
                        if (isset($value['method'])) {
                            $aList = call_user_func([$sClassName, $value['method']]);

                            /* Если значения по умолчанию нет в списке, выделяем первый */
                            if (!isset($aList[$value['default']]) && count($aList)) {
                                $value['default'] = key($aList);
                            }
                        }

                        $oForm->fieldSelect('params_' . $aVal['name'] . ':' . $k, \Yii::t('import', $value['title']), $aList, ['groupTitle' => $sGroup], false);
                    } elseif ($value['viewtype'] == 'show' && ($aVal['type'] == 'File' || $aVal['type'] == 'FileLink')) {
                        $aData['import_upload_formats'] = SysVar::get('import_upload_formats');
                        $oForm->fieldShow('import_upload_formats', \Yii::t('import', 'import_upload_formats'), 's', ['groupTitle' => \Yii::t('import', 'allowed_formats_upload')]);
                    } else {
                        $oForm->field('params_' . $aVal['name'] . ':' . $k, \Yii::t('import', $value['title']), $value['viewtype'], ['groupTitle' => $sGroup]);
                    }

                    $aData['params_' . $aVal['name'] . ':' . $k] = (isset($aVal['params'][$k])) ? $aVal['params'][$k] : $value['default'];
                }
            }
        }

        $oForm->setValue($aData);
    }

    /**
     * Статус
     *
     * @param $item
     *
     * @return string
     */
    public static function getStatus($item)
    {
        $aList = \skewer\base\queue\Api::getStatusList();

        // вместо заморожена, писать в работе
        if ($item['status'] == Task::stFrozen) {
            $item['status'] = Task::stProcess;
        }

        return (isset($aList[$item['status']])) ? $aList[$item['status']] : '';
    }
}
