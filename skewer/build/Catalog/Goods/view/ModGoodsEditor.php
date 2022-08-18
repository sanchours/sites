<?php

namespace skewer\build\Catalog\Goods\view;

use skewer\base\ui\builder\FormBuilder;
use skewer\build\Catalog\Goods\SeoGood;
use skewer\build\Catalog\Goods\SeoGoodModifications;
use skewer\components\catalog;
use skewer\components\seo\Api;
use yii\helpers\ArrayHelper;

/**
 * Построение интерфейса редактирование модификации товара
 * Class ModGoodsEditor.
 */
class ModGoodsEditor extends GoodsEditor
{
    protected $aUniqFields = ['id', 'alias', 'active'];

    /**
     * @throws \Exception
     */
    public function build()
    {
        // задание набора полей для интерфейса
        $fields = $this->initForm(['active'], $this->aUniqFields, true);

        /** @var catalog\GoodsRow $oGoodsRow */
        $oGoodsRow = $this->model->getGoodsRow();

        // Флаг существования каталожной позиции
        $bGoodsExists = $oGoodsRow->getRowId();

        // кнопки
        $this->_form->buttonSave('EditModificationsItem');
        $this->_form->buttonCancel('ModificationsItems');

        $this->filterByActive($fields, $bGoodsExists);
        $this->filterByVisible();

        if ($this->model->getGoodsRow()->getRowId()) {
            $this->_form->buttonSeparator();
            $this->_form->buttonDelete('DeleteModificationsItem');
        }

        $this->_form->useSpecSectionForImages();

        $aData = $this->getGoodData();

        // контент
        $this->_form->setValue($aData);

        $this->setSeoDataInGalleryField(
            $this->model->getGoodsRow()->getRowId(),
            SeoGoodModifications::className()
        );

        $this->setSeoBlock($aData['id']);
    }

    /** {@inheritdoc} */
    protected function setSeoBlock($iGoodId)
    {
        /** @var bool Это общий список товаров(Фильтр = Раздел:Все)? $bIsCommonList */
        $bIsCommonList = !(bool) $this->_module->getCurrentSection();

        if (!$bIsCommonList) {
            $bIsNewGood = !(bool) $iGoodId;

            $aGood = $bIsNewGood
                ? SeoGood::getBlankGood($this->_module->getCardName())
                : catalog\GoodsSelector::get($iGoodId);

            Api::appendExtForm($this->_form, new SeoGoodModifications(ArrayHelper::getValue($aGood, 'id', 0), $this->_module->getCurrentSection(), $aGood), $this->_module->getCurrentSection(), []);
        } else {
            $this->_form->fieldWithValue('warning_text', \Yii::t('SEO', 'warning'), 'show', \Yii::t('SEO', 'warning_text'), ['groupTitle' => \Yii::t('SEO', 'group_title'), 'groupType' => FormBuilder::GROUP_TYPE_COLLAPSIBLE]);
        }
    }
}
