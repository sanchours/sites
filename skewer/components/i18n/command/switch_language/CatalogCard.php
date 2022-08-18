<?php

namespace skewer\components\i18n\command\switch_language;

use skewer\components\catalog;

/**
 * Перевод карточки товаров.
 */
class CatalogCard extends Prototype
{
    private $aCards = [
        catalog\Card::DEF_BASE_CARD => 'cart_base_title',
        'group1' => 'cart_ext_title',
    ];

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $aCards = catalog\Card::getGoodsCards(true);

        if ($aCards) {
            foreach ($aCards as $oCard) {
                /* Смена имени карточки */
                if (isset($this->aCards[$oCard->name])) {
                    $oCard->title = \Yii::t('data/catalog', $this->aCards[$oCard->name], [], $this->getNewLanguage());
                    $oCard->save();
                }

                /* Названия полей */
                foreach ($oCard->getFields() as $oField) {
                    $sTitle = \Yii::t('data/catalog', 'field_' . $oField->name . '_title', [], $this->getNewLanguage());
                    if ($sTitle != 'field_' . $oField->name . '_title') {
                        $oField->title = $sTitle;
                        $oField->save();
                    }

                    /* Для цены нужно сменить  */
                    if ($oField->name == 'price') {
                        $oField->setAttr(catalog\Attr::MEASURE, \Yii::t('data/catalog', 'field_price_measure', [], $this->getNewLanguage()));
                    }
                }

                catalog\Card::build($oCard->id);
            }
        }

        /** Названия групп */
        $aGroups = $this->getGroupList();
        if ($aGroups) {
            foreach ($aGroups as $oGroup) {
                $sTitle = \Yii::t('data/catalog', 'group_' . $oGroup->name . '_title', [], $this->getNewLanguage());
                if ($sTitle != 'group_' . $oGroup->name . '_title') {
                    $oGroup->title = $sTitle;
                    $oGroup->save();
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
    }

    /**
     * @return catalog\model\FieldGroupRow[]
     */
    private function getGroupList()
    {
        return catalog\model\FieldGroupTable::find()->get();
    }
}
