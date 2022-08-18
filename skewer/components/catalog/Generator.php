<?php

namespace skewer\components\catalog;

use skewer\base\ft\Editor;
use skewer\components\filters\FilterPrototype;
use skewer\components\gallery\Profile;
use yii\base\Component;

/**
 * Класс для генерации каталожного окружения по умолчанию
 * Class Generator.
 */
class Generator extends Component
{
    public static function genBaseCard()
    {
        $base = self::createBaseCard();

        $iControlsGroup = self::createGroup('controls', \Yii::t('data/catalog', 'group_controls_title', [], \Yii::$app->language));

        foreach (self::getBaseFields($iControlsGroup) as $aField) {
            self::createField($base->id, $aField);
        }

        $base->updCache();

        return $base->save();
    }

    /**
     * @param string $name
     * @param string $title
     *
     * @return model\EntityRow
     */
    public static function createBaseCard($name = Card::DEF_BASE_CARD, $title = '')
    {
        if (!$title) {
            $title = \Yii::t('data/catalog', 'cart_base_title', [], \Yii::$app->language);
        }

        $oCard = Card::get();
        $oCard->setData([
            'name' => $name,
            'title' => $title,
            'type' => Card::TypeBasic,
            'module' => Card::ModuleName,
        ]);
        $oCard->save();

        return $oCard;
    }

    /**
     * @param $card
     * @param $name
     * @param $title
     *
     * @return model\EntityRow
     */
    public static function createExtCard($card, $name, $title = '')
    {
        $oCard = Card::get();
        $oCard->setData([
            'name' => $name,
            'title' => $title,
            'parent' => $card,
            'type' => Card::TypeExtended,
            'module' => Card::ModuleName,
        ]);
        $oCard->save();

        return $oCard;
    }

    public static function createGroup($name, $title = '')
    {
        $oGroup = Card::getGroup();
        $oGroup->setData([
            'name' => $name,
            'title' => $title,
        ]);

        return $oGroup->save();
    }

    public static function createField($card, $data)
    {
        $oField = Card::getField();
        $oField->setData($data);
        $oField->entity = $card;
        $oField->save();

        if (isset($data['attr'])) {
            foreach ($data['attr'] as $attr => $val) {
                $oField->setAttr($attr, $val);
            }
        }

        if (isset($data['validator'])) {
            $oField->setValidator($data['validator']);
        }

        return $oField->id;
    }

    protected static function getBaseFields($iControlsGroup = 2)
    {
        return [
            [
                'name' => 'title',
                'title' => $title = \Yii::t('data/catalog', 'field_title_title', [], \Yii::$app->language),
                'group' => 0,
                'editor' => 'string',
                'attr' => [
                    Attr::SHOW_IN_SORTPANEL => 1,
                    Attr::SHOW_IN_MODIFICATION => 1,
                    Attr::SHOW_IN_QUICKVIEW => 1,
                ],
                'validator' => 'set',
                'prohib_del' => 1,
                'no_edit' => 1,
            ], [
                'name' => 'article',
                'title' => $title = \Yii::t('data/catalog', 'field_article_title', [], \Yii::$app->language),
                'group' => 0,
                'attr' => [
                    Attr::SHOW_IN_TABLE => 1,
                    Attr::SHOW_IN_MODIFICATION => 1,
                    Attr::SHOW_IN_QUICKVIEW => 1,
                ],
                'editor' => 'string',
                'prohib_del' => 1,
                'no_edit' => 1,
            ], [
                'name' => 'alias',
                'title' => $title = \Yii::t('data/catalog', 'field_alias_title', [], \Yii::$app->language),
                'group' => 0,
                'editor' => 'string',
                'validator' => 'unique',
                'prohib_del' => 1,
                'no_edit' => 1,
            ], [
                'name' => 'gallery',
                'title' => \Yii::t('data/catalog', 'field_gallery_title', [], \Yii::$app->language),
                'group' => 0,
                'editor' => 'gallery',
                'link_id' => Profile::getDefaultId(Profile::TYPE_CATALOG), // Установка профиля по умолчанию
                'widget' => FilterPrototype::TYPE_GALLERY,
                'attr' => [
                    Attr::SHOW_IN_MODIFICATION => 1,
                    Attr::SHOW_IN_QUICKVIEW => 1,
                ],
            ], [
                'name' => 'announce',
                'title' => \Yii::t('data/catalog', 'field_announce_title', [], \Yii::$app->language),
                'group' => 0,
                'attr' => [
                    Attr::SHOW_IN_QUICKVIEW => 1,
                ],
                'editor' => 'wyswyg',
                'prohib_del' => 1,
                'no_edit' => 1,
            ], [
                'name' => 'obj_description',
                'title' => \Yii::t('data/catalog', 'field_obj_description_title', [], \Yii::$app->language),
                'group' => 0,
                'editor' => 'wyswyg',
                'attr' => [
                    Attr::SHOW_IN_LIST => 0,
                    Attr::SHOW_IN_TAB => 1,
                ],
                'prohib_del' => 1,
                'no_edit' => 1,
            ], [
                'name' => 'price',
                'title' => \Yii::t('data/catalog', 'field_price_title', [], \Yii::$app->language),
                'group' => 0,
                'editor' => 'money',
                'attr' => [
                    Attr::SHOW_IN_SORTPANEL => 1,
                    Attr::SHOW_IN_TABLE => 1,
                    Attr::MEASURE => \Yii::t('data/catalog', 'field_price_measure', [], \Yii::$app->language),
                    Attr::SHOW_IN_MODIFICATION => 1,
                    Attr::SHOW_IN_QUICKVIEW => 1,
                ],
                'prohib_del' => 1,
            ], [
                'name' => 'old_price',
                'title' => \Yii::t('data/catalog', 'field_old_price_title', [], \Yii::$app->language),
                'group' => 0,
                'editor' => 'string',
                'attr' => [Attr::ACTIVE => 0,
                           Attr::SHOW_IN_QUICKVIEW => 1,
                ],
            ], [
                'name' => 'measure',
                'title' => \Yii::t('data/catalog', 'field_measure_title', [], \Yii::$app->language),
                'group' => 0,
                'editor' => 'string',
                'attr' => [Attr::SHOW_IN_QUICKVIEW => 1],
            ], [
                'name' => 'add_gallery',
                'title' => \Yii::t('data/catalog', 'field_add_gallery_title', [], \Yii::$app->language),
                'group' => 0,
                'editor' => Editor::GALLERY,
                'link_id' => Profile::getDefaultId(Profile::TYPE_CATALOG_ADD),
                'widget' => FilterPrototype::TYPE_FOTORAMA,
                'no_edit' => 0,
                'prohib_del' => 1,
                'attr' => [
                    Attr::ACTIVE => 0,
                    Attr::SHOW_IN_TAB => 1,
                    Attr::SHOW_IN_DETAIL => 1,
                    Attr::SHOW_IN_LIST => 0,
                ],
                'prohib_del' => 1,
            ], [
                'name' => 'active',
                'title' => \Yii::t('data/catalog', 'field_active_title', [], \Yii::$app->language),
                'group' => $iControlsGroup,
                'editor' => 'check',
                'def_value' => 1,
                'prohib_del' => 1,
            ], [
                'name' => 'buy',
                'title' => \Yii::t('data/catalog', 'field_buy_title', [], \Yii::$app->language),
                'group' => $iControlsGroup,
                'editor' => 'check',
                'def_value' => 1,
                'attr' => [
                    Attr::SHOW_IN_MODIFICATION => 1,
                    Attr::SHOW_IN_QUICKVIEW => 1,
                ],
            ], [
                'name' => 'on_main',
                'title' => \Yii::t('data/catalog', 'field_on_main_title', [], \Yii::$app->language),
                'group' => $iControlsGroup,
                'editor' => 'check',
                'attr' => [Attr::SHOW_IN_LIST => 0],
                'prohib_del' => 1,
            ], [
                'name' => 'hit',
                'title' => \Yii::t('data/catalog', 'field_hit_title', [], \Yii::$app->language),
                'group' => $iControlsGroup,
                'editor' => 'check',
                'prohib_del' => 1,
            ], [
                'name' => 'new',
                'title' => \Yii::t('data/catalog', 'field_new_title', [], \Yii::$app->language),
                'group' => $iControlsGroup,
                'editor' => 'check',
                'prohib_del' => 1,
            ], [
                'name' => 'discount',
                'title' => \Yii::t('data/catalog', 'field_discount_title', [], \Yii::$app->language),
                'group' => $iControlsGroup,
                'editor' => 'check',
                'prohib_del' => 1,
            ],
        ];
    }
}
