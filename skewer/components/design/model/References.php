<?php

namespace skewer\components\design\model;

use skewer\base\orm\Query;
use skewer\components\ActiveRecord\ActiveRecord;

/**
 * This is the model class for table "css_data_references".
 *
 * @property string $ancestor
 * @property string $descendant
 * @property int $active
 */
class References extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'css_data_references';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ancestor', 'descendant'], 'required'],
            [['active'], 'boolean'],
            [['ancestor', 'descendant'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ancestor' => 'Ancestor',
            'descendant' => 'Descendant',
            'active' => 'Active',
        ];
    }

    /**
     * Сохранение связей.
     *
     * @param array $aItems
     */
    public static function saveReferences(array $aItems)
    {
        /**
         * Запрос существующих
         * Связей не много, запросим все разом, вводить около сотни условий в запрос не хочется.
         */
        $aCurItems = Query::SelectFrom('css_data_references')->getAll();

        foreach ($aItems as $key => $item) {
            /* Убираем те, что уже есть */
            if ($aCurItems) {
                foreach ($aCurItems as $aRecord) {
                    if ($item[0] == $aRecord['ancestor'] && $item[1] == $aRecord['descendant']) {
                        unset($aItems[$key]);
                    }
                }
            }
            /* и дубли уберем */
            foreach ($aItems as $ikey => $aItem) {
                if ($ikey == $key) {
                    continue;
                }
                if ($item[0] == $aItem[0] && $item[1] == $aItem[1]) {
                    unset($aItems[$key]);
                }
            }
        }

        if (count($aItems)) {
            $aData = [];
            $sQuery = '
            INSERT INTO `css_data_references`(`ancestor`, `descendant`, `active`) VALUES
        ';
            $i = 0;
            foreach ($aItems as $aItem) {
                ++$i;
                $sQuery .= "( :a{$i}, :d{$i}, 1),";
                $aData['a' . $i] = $aItem[0];
                $aData['d' . $i] = $aItem[1];
            }
            $sQuery = trim($sQuery, ',');

            Query::SQL($sQuery, $aData);
        }
    }
}
