<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 09.01.2018
 * Time: 10:39.
 */

namespace skewer\components\catalog;

use skewer\base\ft\Editor;

/**
 * API для работы с коллекциями
 * Class Collection.
 */
class Collection extends Entity
{
    /**
     * Создать коллекцию.
     *
     * @param array $data Данные поля
     * @param int $iProfileId Id профиля галереи
     *
     * @throws \Exception
     *
     * @return model\EntityRow
     */
    public static function addCollection($data, $iProfileId)
    {
        $oCard = Card::get();

        $oCard->setData($data);

        $oCard->type = Card::TypeDictionary;
        $oCard->module = Card::DEF_COLLECTION_MODULE;
        $oCard->save();

        // добавляем поле название
        Card::setField($oCard->id, 'title', \Yii::t('collections', 'field_title'));
        Card::setField($oCard->id, 'alias', \Yii::t('collections', 'field_alias'));
        $row = Card::setField($oCard->id, 'gallery', \Yii::t('collections', 'field_gallery'), Editor::GALLERY);
        $row->link_id = $iProfileId;
        $row->save();
        Card::setField($oCard->id, 'info', \Yii::t('collections', 'field_info'), Editor::WYSWYG);

        Card::setField($oCard->id, 'active', \Yii::t('collections', 'field_active'), Editor::CHECK);
        Card::setField($oCard->id, 'on_main', \Yii::t('collections', 'field_on_main'), Editor::CHECK);
        Card::setField($oCard->id, 'last_modified_date', \Yii::t('collections', 'fields_last_modified_date'), Editor::DATETIME);

        // генерим кеш
        $oCard->updCache();

        return $oCard;
    }

    public static function getCollections($iPage = false, $iOnPage = false, &$allCount = 0)
    {
        $query = model\EntityTable::find()
            ->where('type', Card::TypeDictionary)
            ->andWhere('module', Card::DEF_COLLECTION_MODULE);

        if ($iPage !== false && $iOnPage !== false) {
            $query->setCounterRef($allCount)
                ->limit($iOnPage, ($iPage * $iOnPage));
        }

        return $query->getAll();
    }

    /**
     * Отдает запись коллекции по имени.
     *
     * @param string $sName
     *
     * @return bool|model\EntityRow
     */
    public static function getCollection($sName)
    {
        return model\EntityTable::find()
            ->where('type', Card::TypeDictionary)
            ->andWhere('module', Card::DEF_COLLECTION_MODULE)
            ->andWhere('name', $sName)
            ->getOne();
    }

    /**
     * Список коллекций каталога.
     *
     * @return array
     */
    public static function getCollectionList()
    {
        $aOut = [];
        foreach (self::getCollections() as $oEntity) {
            $aOut[$oEntity->id] = $oEntity->title;
        }

        return $aOut;
    }
}
