<?php

namespace skewer\build\Adm\Gallery;

/**
 * Класс работы с временными файлами.
 *
 * id
 * name
 * value
 * add_date
 */
class TmpModule
{
    /**
     * Создает запись во временной таблице.
     *
     * @static
     *
     * @param $sName
     * @param $sValue
     *
     * @return int
     */
    public static function create($sName, $sValue)
    {
        \yii\db\ActiveRecord::getDb()->createCommand('INSERT INTO `photogallery_tmp` SET `id`=NULL, `name`=:name, `value`=:value', [
            'name' => $sName,
            'value' => $sValue,
            //'add_date' => date("Y-m-d H:i:s", time())
        ])->execute();

        return \yii\db\ActiveRecord::getDb()->getLastInsertID();
    }

    /**
     * Отдает запись по id.
     *
     * @static
     *
     * @param $iId
     *
     * @return null|array
     */
    public static function getById($iId)
    {
        $aResult = \yii\db\ActiveRecord::getDb()->createCommand('SELECT `id`, `name`, `value` FROM `photogallery_tmp` WHERE `id`=:id', ['id' => $iId])->queryOne();

        return $aResult ? $aResult : null;
    }

    /**
     * Удаляет запись по id.
     *
     * @static
     *
     * @param array|int $mIdList
     *
     * @return int
     */
    public static function delById($mIdList)
    {
        if (!$mIdList) {
            return 0;
        }
        if (is_numeric($mIdList)) {
            $mIdList = [$mIdList];
        }

        $sQuery = sprintf(
            'DELETE FROM `photogallery_tmp` WHERE `id` IN (%s)',
            implode(',', $mIdList)
        );

        return \yii\db\ActiveRecord::getDb()->createCommand($sQuery)->execute();
    }

    /**
     * Отдает список записей, срок давности которых превышает заданный порог.
     *
     * @param int $iDays
     *
     * @throws \Exception
     *
     * @return array
     */
    public static function getOldRows($iDays = 2)
    {
        return \yii\db\ActiveRecord::getDb()->createCommand(
            'SELECT `id`, `name`, `value` FROM `photogallery_tmp` WHERE `add_date` < :date',
            ['date' => date('Y-m-d H:i:s', strtotime("-{$iDays} days"))]
        )->queryAll();
    }

    /**
     * Отдает флаг разрешения запуска сборщика мусора.
     *
     * @static
     *
     * @return bool
     */
    public static function allowStartScavenger()
    {
        return (bool) (random_int(0, 5) === 5);
    }
}
