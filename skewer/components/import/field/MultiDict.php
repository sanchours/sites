<?php
/**
 * Created by PhpStorm.
 * User: vinok
 * Date: 12.01.2017
 * Time: 11:17.
 */

namespace skewer\components\import\field;

use skewer\components\catalog;
use yii\base\UserException;

class MultiDict extends Dict
{
    protected static $parameters = [
        'create' => [
            'title' => 'field_dict_create',
            'datatype' => 'i',
            'viewtype' => 'check',
            'default' => 0,
        ],
        'delimiterMultiDict' => [
            'title' => 'field_multiDict_delimiter',
            'datatype' => 's',
            'viewtype' => 'string',
            'default' => ',',
        ],
    ];

    /**
     * @var string разделитель для мультисправочника
     */
    protected $delimiterMultiDict = ',';

    /**
     * Отдает значения на сохранение в запись товара.
     *
     * @throws UserException
     *
     * @return mixed
     */
    public function getValue()
    {
        $values = implode($this->delimiterMultiDict, $this->values);
        $aVal = explode($this->delimiterMultiDict, $values);

        //массив id элементов, которые сохраняются в запись товара
        $aIds = [];

        if ($this->sCardDictId) {
            foreach ($aVal as $val) {
                $val = trim($val);

                //ищем в справочнике
                $aElement = $this->getFromCache($val);

                //Если нашли запись в справочнике, то добавляем её id в итоговый массив,
                //если нет, то добавляем запись в справочник и добавляем её id в итоговый массив
                if ($aElement) {
                    $aIds[] = $aElement['id'];
                } elseif ($this->create and $val != '') {
                    $mId = catalog\Dict::setValue($this->sCardDictId, [
                        'title' => $val,
                    ]);
                    if ($mId) {
                        //Обновим кэш значений справочника
                        self::$aCache[$this->sCardDictId][$val] = [
                            'id' => $mId,
                            'title' => $val,
                        ];

                        $aIds[] = $mId;
                    }
                }
            }

            //Возвращаем массив id, если он не пустой
            if (!empty($aIds)) {
                $aIds = array_unique($aIds);

                return implode(',', $aIds);
            }
        }

        return '';
    }
}
