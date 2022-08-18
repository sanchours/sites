<?php

namespace skewer\build\Catalog\Filters;

use skewer\build\Catalog\Filters\model\FilterSettings4Card;
use skewer\components\catalog\Card;
use skewer\components\filters;
use yii\helpers\ArrayHelper;

class Api
{
    public static function parseSettings4AdminInterface($aItem)
    {
        $aItem['title'] = \Yii::t('filters', 'name_setting_for_card', ['cardTitle' => Card::getTitle($aItem['card_id'])]);
        $aItem['info'] = filters\Api::getDescriptionSeoLabelsByCard($aItem['card_id']);

        return $aItem;
    }

    public static function getAvailableCards()
    {
        // Все карточки
        $aGoodsCardList = Card::getGoodsCardList();

        $aFilterSettings = FilterSettings4Card::find()->asArray()->all();

        // Уже выбранные карточки
        $aBusyCard = ArrayHelper::map($aFilterSettings, 'card_id', 'card_id');

        // Доступные для выбора карточки
        $aAvailableCards = array_diff_key($aGoodsCardList, $aBusyCard);

        return $aAvailableCards;
    }
}
