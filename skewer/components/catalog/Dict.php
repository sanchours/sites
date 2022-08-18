<?php

namespace skewer\components\catalog;

use skewer\base\ft;
use skewer\base\orm\Query;
use skewer\base\SysVar;
use skewer\components\catalog;
use skewer\components\gallery\Album;
use skewer\helpers\Transliterate;
use yii\base\UserException;

/** Апи для работы со справочниками */
class Dict
{
    public static $aNotRemoveField = ['title', 'alias'];

    /** @var string $nameVariable -  параметр в SysVars для записи запрещённых к удалению справочников */
    public static $nameVariable = 'banDelDict';

    public static $banEditDict = 'banEditDict';

    /**
     * @param $data
     * @param $nameModule
     *
     * @throws \Exception
     * used Dictionary\Module::actionDictionaryAdd
     *
     * @return model\EntityRow
     */
    public static function addDictionary($data, $nameModule)
    {
        $oCard = Card::get();
        $oCard->setData($data);

        $oCard->type = Card::TypeDictionary;
        $oCard->module = $nameModule;
        $oCard->save();

        // добавляем поле название
        Card::setField($oCard->id, 'title', \Yii::t('dict', 'title'));

        // добавляем поле сортировки
        Card::setField($oCard->id, Card::FIELD_SORT, Card::FIELD_SORT, ft\Editor::INTEGER);

        // поле - alias
        Card::setField($oCard->id, 'alias', \Yii::t('dict', 'alias'), ft\Editor::STRING);
        
        if (key_exists('picture', $data)) {
            Card::setField($oCard->id, 'picture', \Yii::t('dict', 'picture'), ft\Editor::GALLERY);
        }
        
        // генерим кеш
        $oCard->updCache();

        return $oCard;
    }

    /**
     * Все справочники.
     *
     * @param $nameLayer - имя слоя {Catalog,Tool}
     *
     * @return model\EntityRow[]
     */
    public static function getDictionaries($nameLayer)
    {
        return model\EntityTable::find()
            ->where('type', Card::TypeDictionary)
            ->andWhere('module', $nameLayer)
            ->getAll();
    }

    /**
     * Список справочников с id в качестве ключей.
     *
     * @var - имя слоя {Catalog|Tool}
     *
     * @param mixed $nameLayer
     *
     * @return array
     */
    public static function getDictAsArray($nameLayer)
    {
        $aOut = [];
        foreach (self::getDictionaries($nameLayer) as $oEntity) {
            $aOut[$oEntity->id] = $oEntity->title;
        }

        return $aOut;
    }

    /**
     * Список справочников с системными именами в качестве ключей.
     *
     * @var - имя слоя {Catalog|Tool}
     *
     * @param mixed $nameLayer
     *
     * @return array
     */
    public static function getDictArrayWithName($nameLayer)
    {
        $aOut = [];
        foreach (self::getDictionaries($nameLayer) as $oEntity) {
            $aOut[$oEntity->name] = $oEntity->title;
        }

        return $aOut;
    }

    /**
     * Получение справочника по названию и имени модуля.
     *
     * @param $sTitle
     * @param $sModuleName
     *
     * @return array
     */
    public static function getDictByTitle($sTitle, $sModuleName)
    {
        return catalog\model\EntityTable::find()
            ->where('title', $sTitle)
            ->andWhere('module', $sModuleName)
            ->andWhere('type', Card::TypeDictionary)
            ->getOne();
    }

    /**
     * Получение id справочника по системному имени и имени модуля.
     *
     * @param $sName
     * @param $sModuleName
     *
     * @return array
     */
    public static function getDictIdByName($sName, $sModuleName)
    {
        $aDict = catalog\model\EntityTable::find()
            ->where('name', $sName)
            ->andWhere('module', $sModuleName)
            ->andWhere('type', Card::TypeDictionary)
            ->asArray()
            ->getOne();

        return ($aDict) ? $aDict['id'] : '';
    }

    /**
     * Получение значений из справочника.
     *
     * @param int|string $mCardId Id карточки справочника
     * @param string $sTitle Искомое значение справочника
     * @param bool $bAsArray Получить в виде массива
     *
     * @throws UserException
     *
     * @return array|bool|\skewer\base\orm\ActiveRecord
     */
    public static function getValByTitle($mCardId, $sTitle, $bAsArray = false)
    {
        $oTableDict = self::getTableDict($mCardId);

        $oQuery = $oTableDict->find()
            ->where('title', $sTitle);

        if ($bAsArray) {
            $oQuery->asArray();
        }

        return $oQuery->getOne();
    }

    /**
     * Получение значений из справочника.
     *
     * @param int|string $mCardId Id карточки справочника
     * @param array|int $mId Идентификатор получаемой записи или массив id
     * @param bool $bAsArray Получить в виде массива
     *
     * @throws UserException
     *
     * @return array|bool|\skewer\base\orm\ActiveRecord
     */
    public static function getValues($mCardId, $mId = 0, $bAsArray = false)
    {
        $oTableDict = self::getTableDict($mCardId);
        $sFieldSort = catalog\Card::FIELD_SORT;

        $oQuery = $oTableDict->find();

        if (isset($oTableDict->getNewRow()->{$sFieldSort})) {
            $oQuery->order($sFieldSort);
        }

        if ($mId) {
            $oQuery->where('id', $mId);
        }

        if ($bAsArray) {
            $oQuery->asArray();
        }

        return ($mId and !is_array($mId)) ? $oQuery->getOne() : $oQuery->getAll();
    }

    /**
     * Получение значений из справочника.
     *
     * @param int|string $mCardId Id карточки справочника
     * @param string $sValue Искомое значение справочника
     * @param bool $bAsArray Получить в виде массива
     * @param string $sNameField название поискового поля
     *
     * @throws UserException
     *
     * @return array|bool|\skewer\base\orm\ActiveRecord
     */
    public static function getValByString($mCardId, $sValue, $bAsArray = false, $sNameField = 'title')
    {
        $oTableDict = self::getTableDict($mCardId);

        $oQuery = $oTableDict->find()
            ->where($sNameField, $sValue);

        if ($bAsArray) {
            $oQuery->asArray();
        }

        return $oQuery->getOne();
    }

    /**
     * Достает записи по ID.
     *
     * @param $mCardId
     * @param $mId
     *
     * @throws UserException
     *
     * @return mixed
     */
    public static function getValById($mCardId, $mId)
    {
        $oTableDict = self::getTableDict($mCardId);

        if (is_array($mId)) {
            $aIds = $mId;
        } else {
            $aIds = [$mId];
        }

        $oQuery = $oTableDict->find()
            ->where('id IN ?', $aIds)
            ->asArray();

        return $oQuery->getAll();
    }

    /**
     * Обновление/добавление значения в справочнике.
     *
     * @param int|string $mCardId Id карточки справочника
     * @param array $aData Данные
     * @param int $iId Идентификатор редактируемой записи
     *
     * @throws UserException
     * @throws \Exception
     *
     * @return int Возвращает id записи в случае успеха
     */
    public static function setValue($mCardId, array $aData, $iId = 0)
    {
        $oTableDict = self::getTableDict($mCardId);

        //генерация alias
        if (!empty($aData['alias'])) {
            $sTmpAlias = Transliterate::change($aData['alias']);
        } else {
            $sTmpAlias = Transliterate::change($aData['title']);
        }

        $sTmpAlias = Transliterate::changeDeprecated($sTmpAlias);
        $sTmpAlias = Transliterate::mergeDelimiters($sTmpAlias);
        $sTmpAlias = trim($sTmpAlias, '-');

        $sTmpAlias = self::getUniqAlias($oTableDict, $sTmpAlias, $iId);

        $aData['alias'] = $sTmpAlias;

        // Установить старший индекс сортировки новому значению справочника
        if (!$iId) {
            $oItem = $oTableDict->getNewRow();

            if ($oTableDict->getModel()->getAttr('priority_sort') == '1') {
                $aLastPriority = \Yii::$app->getDb()->createCommand(
                    '
                    SELECT MIN(`' . catalog\Card::FIELD_SORT . '`)
                    FROM ' . $oTableDict->getTableName()
                )->query()->read();
    
                $aData[catalog\Card::FIELD_SORT] = (int) reset($aLastPriority) - 1;
            } else {
                $aLastPriority = \Yii::$app->getDb()->createCommand(
                    '
                    SELECT MAX(`' . catalog\Card::FIELD_SORT . '`)
                    FROM ' . $oTableDict->getTableName()
                )->query()->read();
    
                $aData[catalog\Card::FIELD_SORT] = (int) reset($aLastPriority) + 1;
            }
        } elseif (!$oItem = $oTableDict->find($iId)) {
            throw new UserException(\Yii::t('dict', 'error_row_not_found'));
        }
        $oItem->setData($aData);

        if ($oItem->save()) {
            \Yii::$app->router->updateModificationDateSite();

            return $oItem->id;
        }

        throw new UserException($oItem->getError());
    }

    protected static function getUniqAlias($oTableDict, $sAlias, $iId = 0)
    {
        $flag = Query::SelectFrom($oTableDict->getTableName())
            ->where('alias', $sAlias);
        if ($iId != 0) {
            $flag->andWhere('id!=?', (int) $iId);
        }

        $flag = (bool) $flag->getCount();

        if (!$flag) {
            return $sAlias;
        }

        preg_match('/^(\S+)(-\d+)?$/Uis', $sAlias, $res);
        $sTplAlias = $res[1] ?? $sAlias;
        $iCnt = isset($res[2]) ? -(int) $res[2] : 0;
        while (mb_substr($sTplAlias, -1) == '-') {
            $sTplAlias = mb_substr($sTplAlias, 0, mb_strlen($sTplAlias) - 1);
        }

        do {
            ++$iCnt;
            $sAlias = $sTplAlias . '-' . $iCnt;

            $flag = Query::SelectFrom($oTableDict->getTableName())
                ->where('alias', $sAlias);
            if ($iId != 0) {
                $flag->andWhere('id!=?', $iId);
            }

            $flag = (bool) $flag->getCount();
        } while ($flag);

        return $sAlias;
    }

    /**
     * Сортировка объектов справочника.
     *
     * @param int|string $mCardId Id карточки справочника
     * @param array $aItemDrop Перемещаемый объект
     * @param array $aItemTarget Объект, относительно которого идет перемещение
     * @param string $sOrderType Направление переноса
     *
     * @throws UserException
     */
    public static function sortValues($mCardId, array $aItemDrop, array $aItemTarget, $sOrderType = 'before')
    {
        $oTableDict = self::getTableDict($mCardId);

        $sSortField = catalog\Card::FIELD_SORT;

        // Выбираем направление сдвига
        if ($aItemDrop[$sSortField] < $aItemTarget[$sSortField]) {
            $sSign = '-';
            $iNewPos = ($sOrderType == 'after') ? $aItemTarget[$sSortField] : $aItemTarget[$sSortField] - 1;
            $iStartPos = $aItemDrop[$sSortField];
            $iEndPos = ($sOrderType == 'after') ? $aItemTarget[$sSortField] + 1 : $aItemTarget[$sSortField];
        } else {
            $sSign = '+';
            $iNewPos = ($sOrderType == 'after') ? $aItemTarget[$sSortField] + 1 : $aItemTarget[$sSortField];
            $iStartPos = ($sOrderType == 'after') ? $aItemTarget[$sSortField] : $aItemTarget[$sSortField] - 1;
            $iEndPos = $aItemDrop[$sSortField];
        }

        $oTableDict->update(
            ["{$sSortField}={$sSortField}" . $sSign . '?' => 1],
            ["{$sSortField} >?" => $iStartPos,
             "{$sSortField} <?" => $iEndPos, ]
        );

        $oTableDict->update(
            [$sSortField => $iNewPos],
            ['id' => $aItemDrop['id']]
        );
    }

    /**
     * Удаление записи из справочника.
     *
     * @param int|string $mCardId Id карточки справочника
     * @param int $iId Id удаляемой записи
     * @param array $aData Данные удаляемой записи
     *
     * @throws UserException
     * @throws \Exception
     */
    public static function removeValue($mCardId, $iId, $aData = [])
    {
        $oTableDict = self::getTableDict($mCardId);

        if (!$iId) {
            throw new UserException(\Yii::t('dict', 'error_row_not_found'));
        }
        // поиск полей связанных со справочником
        $oEntity = catalog\Card::get($mCardId);

        // для связи -<
        $aFields = catalog\model\FieldTable::find()
            ->where('link_type', ft\Relation::ONE_TO_MANY)
            ->where('link_id', $oEntity->id)
            ->getAll();

        foreach ($aFields as $oField) {
            $oTable = ft\Cache::getMagicTable($oField->entity);

            if (!$oTable) {
                continue;
            }

            // очищаем значения полей
            $oTable->update([$oField->name => ''], [$oField->name => $iId]);
        }

        // для связи ><
        $aFields = catalog\model\FieldTable::find()
            ->where('link_type', ft\Relation::MANY_TO_MANY)
            ->where('link_id', $oEntity->id)
            ->getAll();

        foreach ($aFields as $oField) {
            if (!$oField->entity) {
                continue;
            }

            $model = ft\Cache::get($oField->entity);

            $field = $model->getFiled($oField->name);

            $field->unLinkAllRow($iId);
        }

        // Удаление специализированных полей
        $aData = $aData ?: self::getValues($mCardId, $iId, true);
        foreach ($oEntity->getFields() as $oField) {
            // Удаление альбомов галерей
            if (($oField->editor == ft\Editor::GALLERY) and ($iAlbumId = (int) $aData[$oField->name])) {
                Album::removeAlbum($iAlbumId);
            }
        }

        // удаление значения из справочника
        $oTableDict->delete($iId);
    }

    /**
     * Отдает Id карточки справочника, соответствующую каталожному полю.
     *
     * @param string $sFieldName Имя поля каталожной карточки $sCardName
     * @param string $sCardName Имя каталожной карточки
     *
     * @throws \Exception
     *
     * @return bool|string Идентификатор карточки справочника или false
     */
    public static function getDictIdByCatalogField($sFieldName, $sCardName)
    {
        $oModel = ft\Cache::get($sCardName);

        $oDictField = false;

        foreach ($oModel->getFileds() as $oField) {
            if ($oField->getName() == $sFieldName) {
                $oDictField = $oField;
            }
        }

        if (!$oDictField && $oModel->getType() == Card::TypeExtended) {
            $oParentModel = ft\Cache::get($oModel->getParentId());

            foreach ($oParentModel->getFileds() as $oField) {
                if ($oField->getName() == $sFieldName) {
                    $oDictField = $oField;
                }
            }
        }

        if ($oDictField) {
            $oRel = $oDictField->getModel()->getOneFieldRelation($oDictField->getName());
            if ($oRel) {
                return $oRel->getEntityName();
            }
        }

        return false;
    }

    /**
     * Получить имя таблицы справочника.
     *
     * @param int|string $mCardId Id карточки справочника
     *
     * @throws UserException
     *
     * @return string
     */
    public static function getDictTableName($mCardId)
    {
        if (is_numeric($mCardId)) {
            if ($oTableDict = self::getTableDict($mCardId)) {
                return $oTableDict->getTableName();
            }
        } else {
            return catalog\Entity::getTablePreffix(catalog\Entity::TypeDictionary) . $mCardId;
        }

        return '';
    }

    /**
     * Удалить справочник.
     *
     * @param int|string $mCardId Id карточки справочника
     * @param array $aErrorMessages Список ошибок
     *
     * @throws UserException
     * @throws \Exception
     *
     * @return bool
     */
    public static function removeDict($mCardId, &$aErrorMessages = [])
    {
        if (!$oCardDict = catalog\Card::get($mCardId)) {
            return false;
        }

        $aErrorMessages = [];

        // Обнаружение полей связанных со справочником
        /** @var catalog\model\FieldRow[] $aFields */
        $aFields = catalog\model\FieldTable::find()
            ->where('link_type IN ?', [ft\Relation::ONE_TO_MANY, ft\Relation::MANY_TO_MANY])
            ->where('link_id', $oCardDict->id)
            ->getAll();

        // Если есть связанные поля, то записать ошибки
        foreach ($aFields as $oField) {
            $oCard = catalog\Card::get($oField->entity);
            $aErrorMessages[] = $oField->title . ' (' . \Yii::t('card', 'head_card_name', $oCard->title) . ')';
        }

        if ($aErrorMessages) {
            return false;
        }

        // Удалить каждую запись. Актуально для вычищения галерей
        foreach (self::getValues($mCardId, 0, true) as $aData) {
            self::removeValue($mCardId, $aData['id'], $aData);
        }

        return $oCardDict->delete();
    }

    /**
     * Получить AR таблицы справочника.
     *
     * @param int|string $mCardId Id карточки справочника
     *
     * @throws UserException
     *
     * @return \skewer\base\orm\MagicTable
     */
    public static function getTableDict($mCardId)
    {
        if (!$mCardId) {
            throw new UserException('Card not found!');
        }
        if (!$oTableDict = ft\Cache::getMagicTable($mCardId)) {
            throw new UserException(\Yii::t('dict', 'error_dict_not_found'));
        }

        return $oTableDict;
    }

    /**
     * Получить справочник по системному имени.
     *
     * @param string $sNameDict системное имя справочника
     *
     * @throws UserException
     *
     * @return array
     */
    public static function getDictByName($sNameDict)
    {
        if (!$sNameDict) {
            return [];
        }

        $oEntityDict = catalog\model\EntityTable::getByName($sNameDict);

        if (!$oEntityDict || $oEntityDict->type != Card::TypeDictionary) {
            return [];
        }

        $oTableDict = ft\Cache::getMagicTable($sNameDict);

        return $oTableDict->find()->asArray()->getAll();
    }

    /**
     * Получить по id справочника определенное количество записей из него.
     *
     * @param int $idDict Id карточки справочника
     * @param int $iPage Страница для показа
     * @param int $onPage количество на странице
     * @param &$allCount - общий размер выборки
     *
     * @throws UserException
     *
     * @return array()
     */
    public static function getDictTable($idDict, $iPage, $onPage, &$allCount = 0)
    {
        $sNameTable = self::getDictTableName($idDict);
        $aDict = Query::SelectFrom($sNameTable)
            ->setCounterRef($allCount)
            ->limit($onPage, ($iPage) * $onPage)
            ->order('priority')
            ->getAll();

        return $aDict;
    }

    /**
     * Установка запрета на удаление справочника.
     *
     * @param $nameDict - имя справочника
     *
     * @return bool
     */
    public static function setBanDelDict($nameDict)
    {
        $aBanDelDict = json_decode(SysVar::get(self::$nameVariable));
        if ($aBanDelDict && in_array($nameDict, $aBanDelDict)) {
            return true;
        }

        $aBanDelDict[] = $nameDict;
        $sBanDelDict = json_encode($aBanDelDict);

        return SysVar::set(self::$nameVariable, $sBanDelDict);
    }

    /**
     * Установка запрета на редактирование справочника.
     *
     * @param $nameDict - имя справочника
     *
     * @return bool
     */
    public static function setBanEditDict($nameDict)
    {
        $aBanDelDict = json_decode(SysVar::get(self::$banEditDict));
        if ($aBanDelDict && in_array($nameDict, $aBanDelDict)) {
            return true;
        }

        $aBanDelDict[] = $nameDict;
        $sBanDelDict = json_encode($aBanDelDict);

        return SysVar::set(self::$banEditDict, $sBanDelDict);
    }

    public static function getBanDelDict()
    {
        return json_decode(SysVar::get(self::$nameVariable, '[]'));
    }

    public static function getBanEditDict()
    {
        return json_decode(SysVar::get(self::$banEditDict, '[]'));
    }

    /**
     * Разрешение на удаление справочника.
     *
     * @param $nameDict
     *
     * @return bool
     */
    public static function enableDelDict($nameDict)
    {
        $aBanDelDict = json_decode(SysVar::get(self::$nameVariable));

        if ($aBanDelDict && in_array($nameDict, $aBanDelDict)) {
            unset($aBanDelDict[array_search($nameDict, $aBanDelDict)]);
        }

        $aBanDelDict = array_values($aBanDelDict);

        return SysVar::set(self::$nameVariable, json_encode($aBanDelDict));
    }

    /**
     * Разрешение на редактирование справочника.
     *
     * @param $nameDict
     *
     * @return bool
     */
    public static function enableEditDict($nameDict)
    {
        $aBanDelDict = json_decode(SysVar::get(self::$banEditDict));

        if ($aBanDelDict && in_array($nameDict, $aBanDelDict)) {
            unset($aBanDelDict[array_search($nameDict, $aBanDelDict)]);
        }

        $aBanDelDict = array_values($aBanDelDict);

        return SysVar::set(self::$banEditDict, json_encode($aBanDelDict));
    }
}
