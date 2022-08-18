<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 02.03.2017
 * Time: 16:08.
 */

namespace skewer\build\Tool\Import\view;

use skewer\base\site\Site;
use skewer\base\SysVar;
use skewer\build\Adm\Order\model\Status;
use skewer\components\Exchange\ExchangeSales;
use skewer\components\ext\view\FormView;
use skewer\components\import\ar\ImportTemplate;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

class SettingsTrade extends FormView
{
    /**
     * Выполняет сборку интерфейса.
     */
    public function build()
    {
        $sCommonSettingsGroup = \Yii::t('import', 'common_settings');
        $sOrderSettingsGroup = \Yii::t('import', 'order_settings');
        $sGoodSettingsGroup = \Yii::t('import', 'good_settings');

        $aTemplates = ImportTemplate::find()
            ->asArray()
            ->getAll();

        $aTemplates = ArrayHelper::map($aTemplates, 'id', 'title');

        $this->_form
            ->headText(\Yii::t('import', '1c_headtext', ['domain' => Site::httpDomain()]))
            ->fieldSelect('exchange_active', \Yii::t('import', 'exchange_active'), [\Yii::t('import', 'exchange_denied'), \Yii::t('import', 'exchange_allow')], ['groupTitle' => $sCommonSettingsGroup], false)
            ->fieldSelect('schema_version', \Yii::t('import', 'schema_version'), ExchangeSales::getCommerceMlSchemaVersions(), ['groupTitle' => $sCommonSettingsGroup], false)
            ->fieldInt('file_limit', \Yii::t('import', 'file_limit'), ['groupTitle' => $sCommonSettingsGroup, 'minValue' => 51200])
//            ->fieldCheck('use_zip', 'Использовать архивирование при передаче файлов', ['groupTitle' => $sCommonSettingsGroup])
            ->fieldSelect('id_import_template_goods', \Yii::t('import', 'id_import_template_goods'), $aTemplates, ['groupTitle' => $sGoodSettingsGroup])
            ->fieldSelect('id_import_template_prices', \Yii::t('import', 'id_import_template_prices'), $aTemplates, ['groupTitle' => $sGoodSettingsGroup])
            ->fieldMultiSelect('export_statuses', \Yii::t('import', 'export_statuses'), Status::getListTitle(), [], ['groupTitle' => $sOrderSettingsGroup])
            ->fieldCheck('useCommonContragent', \Yii::t('import', 'useCommonContragent'), ['groupTitle' => $sOrderSettingsGroup])
            ->fieldCheck('updateStatusesCms', \Yii::t('import', 'updateStatusesCms'), ['groupTitle' => $sOrderSettingsGroup])
            ->fieldSelect('status_after_paid', \Yii::t('import', 'status_after_paid'), Status::getListTitle(), ['groupTitle' => $sOrderSettingsGroup])
            ->fieldSelect('status_after_delivery', \Yii::t('import', 'status_after_delivery'), Status::getListTitle(), ['groupTitle' => $sOrderSettingsGroup])
            ->fieldSelect('status_after_delivery_and_paid', \Yii::t('import', 'status_after_delivery_and_paid'), Status::getListTitle(), ['groupTitle' => $sOrderSettingsGroup])

            ->buttonSave('saveSettingsTrade')
            ->buttonBack();

        $this->_form->setValue([
            'exchange_active' => SysVar::get('1c.exchange_active', 0),
            'schema_version' => SysVar::get('1c.schema_version', 0),
            'file_limit' => SysVar::get('1c.file_limit', 51200),
//            'use_zip'      => SysVar::get('1c.use_zip', false),
            'id_import_template_goods' => SysVar::get('1c.id_import_template_goods', false),
            'id_import_template_prices' => SysVar::get('1c.id_import_template_prices', false),
            'export_statuses' => StringHelper::explode(SysVar::get('1c.export_statuses', ''), ',', true, true),
            'useCommonContragent' => SysVar::get('1c.useCommonContragent'),
            'updateStatusesCms' => SysVar::get('1c.updateStatusesCms'),
            'status_after_paid' => SysVar::get('1c.status_after_paid'),
            'status_after_delivery' => SysVar::get('1c.status_after_delivery'),
            'status_after_delivery_and_paid' => SysVar::get('1c.status_after_delivery_and_paid'),
        ]);
    }
}
