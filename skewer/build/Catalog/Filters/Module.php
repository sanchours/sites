<?php

namespace skewer\build\Catalog\Filters;

use skewer\base\ui\ARSaveException;
use skewer\build\Catalog\Filters\model\FilterSettings4Card;
use skewer\build\Catalog\Filters\view\SelectCard;
use skewer\build\Catalog\LeftList\ModulePrototype;
use yii\base\UserException;

class Module extends ModulePrototype
{
    public function actionInit()
    {
        /** @var FilterSettings4Card[] $aItems */
        $aItems = FilterSettings4Card::find()->asArray()->all();

        foreach ($aItems as &$aItem) {
            $aItem = Api::parseSettings4AdminInterface($aItem);
        }

        $this->render(new view\Index([
            'items' => $aItems,
        ]));
    }

    /** Добавление записи */
    protected function actionNew()
    {
        $aAvailableCards = Api::getAvailableCards();

        if (!$aAvailableCards) {
            $this->addError(\Yii::t('filters', 'addition_not_possible'), \Yii::t('filters', 'no_cards_available'));

            return psComplete;
        }

        $this->render(new SelectCard([
            'aGoodsCardList' => $aAvailableCards,
        ]));

        return psComplete;
    }

    /** Установка карточки */
    protected function actionSaveCard()
    {
        $iCardId = $this->getInDataValInt('card_id');

        if (!$iCardId) {
            $this->addError(\Yii::t('filters', 'error'), \Yii::t('filters', 'validation_error', ['filter_card' => \Yii::t('filters', 'filter_card')]));

            return psComplete;
        }

        $oFiltersSettings = new FilterSettings4Card();
        $oFiltersSettings->card_id = $iCardId;
        $oFiltersSettings->save();

        $this->actionShow($oFiltersSettings->id);

        return psComplete;
    }

    /** Редактирование записи */
    protected function actionShow($iId = 0)
    {
        $iItemId = $iId ? $iId : $this->getInDataValInt('id');

        /* @var FilterSettings4Card $oNewsRow */
        if (!($oRow = FilterSettings4Card::findOne(['id' => $iItemId]))) {
            throw new UserException(\Yii::t('news', 'error_row_not_found', [$iItemId]));
        }

        $oRow = Api::parseSettings4AdminInterface($oRow->toArray());

        $this->render(new view\Form([
            'item' => $oRow,
        ]));
    }

    /** Сохранение записи */
    protected function actionSaveFilterSettings4Card()
    {
        $aData = $this->get('data');

        $iIdRow = $this->getInDataValInt('id');

        $oRow = FilterSettings4Card::getNewOrExist(['id' => $iIdRow]);
        $oRow->setAttributes($aData);
        $bRes = $oRow->save();

        if (!$bRes) {
            throw new ARSaveException($oRow);
        }

        $this->actionInit();
    }

    /** Удаление записи */
    protected function actionDelete()
    {
        $iId = $this->getInDataValInt('id');

        FilterSettings4Card::deleteAll(['id' => $iId]);

        $this->actionInit();
    }
}
