<?php

namespace skewer\build\Tool\YandexExport;

use skewer\components\catalog;
use skewer\components\config\InstallPrototype;

class Install extends InstallPrototype
{
    private $iCardId = 1;

    private $iGroupId = false;

    const FieldGroupName = 'yandex';

    public $aListAttribute = [];

    public function getListAttribute()
    {
        $this->aListAttribute = [
            'set' => [
                catalog\Attr::ACTIVE,
                catalog\Attr::SHOW_IN_LIST,
                catalog\Attr::SHOW_IN_DETAIL,
                catalog\Attr::SHOW_TITLE,
            ],
            'not_set' => [
                catalog\Attr::MEASURE,
                catalog\Attr::SHOW_IN_SORTPANEL,
                catalog\Attr::SHOW_IN_PARAMS,
                catalog\Attr::SHOW_IN_TAB,
                catalog\Attr::SHOW_IN_FILTER,
                catalog\Attr::IS_UNIQ,
                catalog\Attr::SHOW_IN_TABLE,
                catalog\Attr::SHOW_IN_CART,
            ],
        ];
    }

    public function addParam($name, $title, $editor, $defValue = '')
    {
        $aData['id'] = 0;
        $aData['title'] = $title;
        $aData['name'] = $name;
        $aData['editor'] = $editor;
        $aData['group'] = $this->iGroupId;
        $aData['entity'] = $this->iCardId;
        $aData['validator'] = '';
        $aData['link_id'] = 0;
        $aData['def_value'] = $defValue;

        foreach ($this->aListAttribute['set'] as $item) {
            $aData['attr_' . $item] = 1;
        }

        foreach ($this->aListAttribute['not_set'] as $item) {
            $aData['attr_' . $item] = '';
        }

        $oField = catalog\Card::getField();
        $oField->setData($aData);
        $oField->save();

        return $oField;
    }

    private function setYandex()
    {
        $this->executeSQLQuery('UPDATE `co_base_card` SET in_yandex =1;');
    }

    public function init()
    {
        $sBaseCard = catalog\Card::DEF_BASE_CARD;
        $oCardRow = catalog\Card::get($sBaseCard);
        if (!$oCardRow) {
            $this->fail('Не найдена базовая карточка!');
        }

        $this->getListAttribute();

        $this->iCardId = $oCardRow->id;

        return true;
    }

    // func

    public function install()
    {
        $oGroup = catalog\Card::getGroup();
        $oGroup->setData([
            'entity' => $this->iCardId,
            'name' => static::FieldGroupName,
            'title' => \Yii::t('data/catalog', 'group_yandex_title', [], \Yii::$app->language),
        ]);
        $oGroup->save();

        $this->iGroupId = $oGroup->id;

        $this->addParam('in_yandex', \Yii::t('data/catalog', 'field_in_yandex_title', [], \Yii::$app->language), 'check', 1);
        $this->addParam('pickup', \Yii::t('data/catalog', 'field_pickup_title', [], \Yii::$app->language), 'check', 1);
        $this->addParam('available', \Yii::t('data/catalog', 'field_available_title', [], \Yii::$app->language), 'check', '');
        $this->addParam('store', \Yii::t('data/catalog', 'field_store_title', [], \Yii::$app->language), 'check', 1);
        $this->addParam('delivery', \Yii::t('data/catalog', 'field_delivery_title', [], \Yii::$app->language), 'check', 1);
        $this->addParam('warranty', \Yii::t('data/catalog', 'field_warranty_title', [], \Yii::$app->language), 'check', '');
        $this->addParam('vendor', \Yii::t('data/catalog', 'field_vendor_title', [], \Yii::$app->language), 'string', '');
        $this->addParam('adult', \Yii::t('data/catalog', 'field_adult_title', [], \Yii::$app->language), 'check', '');

        catalog\Card::build($this->iCardId);

        $this->setYandex();

        $sDir = WEBPATH . 'export';
        if (!is_dir($sDir)) {
            try {
                mkdir($sDir);
                chmod($sDir, 0777);
            } catch (\Exception $e) {
                $this->fail($e->getMessage());

                return false;
            }
        }

        return true;
    }

    // func

    public function uninstall()
    {
        $oGroup = catalog\Card::getGroupByName(static::FieldGroupName);
        if (!$oGroup) {
            return true;
        }

        $aFields = $oGroup->getFields();

        if ($aFields) {
            /*
             * @var catalog\model\FieldRow
             */
            foreach ($aFields as $oField) {
                $oField->delete();
            }
        }

        $oGroup->delete();

        catalog\Card::build($this->iCardId);

        return true;
    }

    // func
}//class
