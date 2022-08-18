<?php

namespace skewer\components\import\field;

use skewer\components\catalog;
use yii\helpers\ArrayHelper;

/**
 * Обработчик поля типа справочник.
 */
class Dict extends Prototype
{
    /** @var bool Создавать новые */
    protected $create = false;

    /*
     * Флаг кэшировать справочники
     */
    public static $bUseDictCache = null;

    protected static $parameters = [
        'create' => [
            'title' => 'field_dict_create',
            'datatype' => 'i',
            'viewtype' => 'check',
            'default' => 0,
        ],
    ];

    protected static $aCache = [];

    /** @var int Id карточки справочника */
    protected $sCardDictId = false;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->getDict();

        if (!$this->sCardDictId) {
            throw new \Exception(\Yii::t('import', 'error_dict_not_found', $this->fieldName));
        }
    }

    /**
     * Отдает значение на сохранение в запись товара.
     *
     * @return mixed
     */
    public function getValue()
    {
        /*Если не установлены данные о кэшировании справочников - установим*/
        if (self::$bUseDictCache === null) {
            self::$bUseDictCache = (bool) $this->config->getParam('use_dict_cache');
        }

        $val = implode(',', $this->values);

        if ($this->sCardDictId) {
            /*
             * Когда мало справочников и мало значений в них, лучше кэшировать справочники и работать с кэшами
             */
            if (self::$bUseDictCache) {
                //ищем в кэше
                $aElement = $this->getFromCache($val);
            } else {
                //выборка напрямую из БД
                $aElement = catalog\Dict::getValByTitle($this->sCardDictId, $val, true);
            }

            if ($aElement) {
                return $aElement['id'];
            }

            //создадим, если надо
            if ($this->create and $val != '') {
                $mId = catalog\Dict::setValue($this->sCardDictId, [
                    'title' => $val,
                ]);

                if ($mId) {
                    /* Если в настройках стоит работа с кэшами, добавим в кэш новый элемент справочника */
                    if (self::$bUseDictCache) {
                        self::$aCache[$this->sCardDictId][$val] = [
                            'id' => $mId,
                            'title' => $val,
                        ];
                    }

                    return $mId;
                }
            }
        }

        return '';
    }

    /**
     * Пытается извлечь значение справочника из кэша.
     *
     * @param $sValue
     *
     * @return bool
     */
    protected function getFromCache($sValue)
    {
        //Перестроим кэш.
        if (!isset(self::$aCache[$this->sCardDictId][$sValue])) {
            $this->buildCache();
        }

        //Дернем значение из перестроенного кэша
        if (isset(self::$aCache[$this->sCardDictId][$sValue])) {
            return self::$aCache[$this->sCardDictId][$sValue];
        }

        return false;
    }

    /**
     * Строит кэш значений таблицы справочника.
     */
    private function buildCache()
    {
        $aDictionary = catalog\Dict::getValues($this->sCardDictId, 0, true);

        $aDictionary = ArrayHelper::index($aDictionary, 'title');

        self::$aCache[$this->sCardDictId] = $aDictionary;
    }

    /**
     * Получение таблицы справочника.
     */
    protected function getDict()
    {
        $this->sCardDictId = catalog\Dict::getDictIdByCatalogField($this->fieldName, $this->getCard());
    }
}
