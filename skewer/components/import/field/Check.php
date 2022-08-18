<?php

namespace skewer\components\import\field;

use skewer\components\catalog\Api;
use skewer\components\import;

/**
 * Обработчик поля типа "Галочка"
 * поле "Галочка" служит для настройки свойств товара
 * "шип, Доставка за 1 день, бесплатный выезд замерщика,
 * негабаритный товар" и т.д.
 */
class Check extends Prototype
{
    /** Не снимать галочку */
    const hideNone = 0;

    /** Снять галочку у всех */
    const hideAll = 1;

    /** Снять галочку у всех внутри карточки */
    const hideFromCard = 2;

    /** По значению */
    const value = 0;

    /** Всем в выгрузке */
    const all = 1;

    /** @var int Скрывать */
    protected $hide = 0;

    /** @var int Значение */
    protected $fromValue = 0;

    /** @var int Значение для перевода в bool, пришедшие из импорта */
    protected $valueText = '';

    /** @var array */
    protected static $parameters = [
        'hide' => [
            'title' => 'field_active_hide',
            'datatype' => 'i',
            'viewtype' => 'select',
            'default' => 0,
            'method' => 'getHideList',
        ],
        'fromValue' => [
            'title' => 'field_active_from_value',
            'datatype' => 'i',
            'viewtype' => 'select',
            'default' => 0,
            'method' => 'getFromValueList',
        ],
        'valueText' => [
            'title' => 'field_check_value_text',
            'datatype' => 's',
            'viewtype' => 'string',
            'default' => '',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        /* Начало импорта */
        if ($this->getImportStatus() == import\Task::importStart) {
            switch ($this->hide) {
                case self::hideAll:
                    /* Скрываем все */
                    Api::disableAll($this->fieldName);
                    break;

                case self::hideFromCard:
                    /* Скрываем все внутри карточки */
                    Api::disableByCard($this->getCard(), $this->fieldName);
                    break;
            }
        }
    }

    /**
     * Операции, выполняемые до сохранения товара.
     */
    public function beforeSave()
    {
    }

    /**
     * Отдает значение на сохранение в запись товара.
     *
     * @return mixed
     */
    public function getValue()
    {
        if ($this->fromValue == self::all) {
            return 1;
        }
        if (!empty($this->valueText)) {
            $valueFromImport = implode(',', $this->values);

            // Убираем пробелы
            $this->valueText = trim($this->valueText);
            $valueFromImport = trim($valueFromImport);

            // Проверяем значения для перевода активности товара в bool
            // Сравниваем значение из импорта и значение из настроек
            if (strcasecmp($valueFromImport, $this->valueText) == 0) {
                return 1;
            }

            return 0;
        }

        return (bool) implode(',', $this->values);
    }

    /**
     * {@inheritdoc}
     */
    public function shutdown()
    {
        /* Конец импорта */
        if ($this->getImportStatus() == import\Task::importFinish) {
        }
    }

    /**
     * Список вариантов скрытия.
     *
     * @return array
     */
    public static function getHideList()
    {
        return [
            self::hideNone => \Yii::t('import', 'field_active_hide_none'),
            self::hideAll => \Yii::t('import', 'field_active_hide_all'),
            self::hideFromCard => \Yii::t('import', 'field_active_hide_from_card'),
        ];
    }

    /**
     * Список вариантов выставления значения.
     *
     * @return array
     */
    public static function getFromValueList()
    {
        return [
            self::value => \Yii::t('import', 'field_active_value'),
            self::all => \Yii::t('import', 'field_active_all'),
        ];
    }
}
