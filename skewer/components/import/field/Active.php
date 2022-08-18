<?php

namespace skewer\components\import\field;

use skewer\base\queue;
use skewer\components\import;

/**
 * Обработчик поля типа "Активность"
 * используется для технических полей товара
 * "активность, купить, наличие, удалить" и т.д.
 */
class Active extends Prototype
{
    /** Не снимать активность */
    const hideNone = 0;

    /** Снять активность у всех */
    const hideAll = 1;

    /** Снять активность у всех внутри карточки */
    const hideFromCard = 2;

    /** По значению */
    const value = 0;

    /** Всем в выгрузке */
    const all = 1;

    /** Не удалять */
    const deleteNone = 0;

    /** Удалять все */
    const deleteAll = 1;

    /** Удалять все внутри карточки */
    const deleteFromCard = 2;

    /** @var int Скрывать */
    protected $hide = 0;

    /** @var int Значение */
    protected $fromValue = 0;

    /** @var int Удаление */
    protected $delete = 0;

    /** @var int Значение для перевода в bool, если товар в выгрузке активен */
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
        'delete' => [
            'title' => 'field_active_delete',
            'datatype' => 'i',
            'viewtype' => 'select',
            'default' => 0,
            'method' => 'getDeleteList',
        ],
        'valueText' => [
            'title' => 'field_active_value_text',
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
    }

    public function getHide()
    {
        return $this->hide;
    }

    /**
     * Операции, выполняемые до сохранения товара.
     */
    public function beforeSave()
    {
        // Если из файла выгрузки пришло пустое значение,
        // то записываем эти товары в лог

        $values = implode(',', $this->values);

        if ((
            !empty($this->valueText) && empty($values)
            &&
                !$this->fromValue //не стоит "выставлять всем значениям в выгрузке"
            )
            ||
            ($this->fromValue && !$this->valueText)) { // или стоит "выставлять всем значениям в выгрузке", НО значение которое ставить = 0) {
            // Получаем имя товара
            $title = $this->getGoodsRow()->getData()['title'];

            // Записываем в лог товары, с некорректными данными об активности
            $this->logger->incParam('not_activity');
            $this->logger->setListParam('not_activity_list', $title);
        }
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

            // Приводим к одному регистру
            $this->valueText = mb_strtolower($this->valueText);
            $valueFromImport = mb_strtolower($valueFromImport);

            // Проверяем значения для перевода активности товара в bool
            // Сравниваем значение из импорта и значение из настроек
            if ($valueFromImport == $this->valueText) {
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
            switch ($this->delete) {
                case self::deleteAll:
                case self::deleteFromCard:

                    $card = '';
                    if ($this->delete == self::deleteFromCard) {
                        $card = $this->getCard();
                    }

                    // Ставим задачу на удаление
                    queue\Api::addTask([
                        'class' => '\skewer\components\import\DeleteTask',
                        'priority' => queue\Task::priorityHigh,
                        'title' => 'Удаление товаров после импорта по шаблону ' . $this->config->getParam('id'),
                        'parent' => $this->getTaskId(),
                        'parameters' => ['field_name' => $this->fieldName, 'card' => $card, 'parentTask' => $this->getTaskId(), 'tpl' => $this->config->getParam('id')],
                    ]);

                    break;
            }
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

    /**
     * Список вариантов удаления.
     *
     * @return array
     */
    public static function getDeleteList()
    {
        return [
            self::deleteNone => \Yii::t('import', 'field_active_delete_none'),
            self::deleteAll => \Yii::t('import', 'field_active_delete_all'),
            self::deleteFromCard => \Yii::t('import', 'field_active_delete_from_card'),
        ];
    }
}
