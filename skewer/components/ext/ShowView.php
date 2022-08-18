<?php

namespace skewer\components\ext;

use skewer\base\ui;

/**
 * Тип автопостроителя для вывода текстовых наборов данных
 * (не редактируемых пар название-значение).
 *
 * Задается модель для автоматического подтягивания имен полей
 * В выво попадают только те поля, которые явно есть в массиве значений,
 * а не все из модели как в форме
 */
class ShowView extends ModelPrototype implements ui\state\ShowInterface
{
    /** @var array[] набор данных */
    protected $aItems = [];

    /** @var string имя файла для обновления полей вывода */
    protected $sJSFileUpdater = '';

    /**
     * Возвращает имя компонента.
     *
     * @return string
     */
    public function getComponentName()
    {
        return 'Show';
    }

    /**
     * Добавление переменных.
     *
     * @param $aItems
     */
    public function setValues($aItems)
    {
        foreach ($aItems as $mKey => $mVal) {
            // если массив данных
            if (is_array($mVal)) {
                if (!isset($mVal['name'])) {
                    $mVal['name'] = $mKey;
                }
                $this->addItemArray($mVal);
            }

            // если обычный элемент
            else {
                $this->addItem($mKey, $mVal);
            }
        }
    }

    /**
     * добавляет пару значений название-значение в список вывода.
     *
     * @param null|string $sName системное имя
     * @param string $sTitle название
     * @param string $sValue значение
     */
    public function addItem($sName, $sValue, $sTitle = '')
    {
        if ($sName === null) {
            $sName = 'item_' . count($this->aItems);
        }

        if (!$sTitle and $this->hasField($sName)) {
            $sTitle = $this->getField($sName)->getTitle();
        }

        $this->addItemArray([
            'title' => $sTitle,
            'value' => $sValue,
            'name' => $sName,
        ]);
    }

    /**
     * Добавляет массив данных записи.
     *
     * @param array $aData
     */
    public function addItemArray($aData)
    {
        // набор базовых данных
        $aBase = [
            'title' => $sName = 'item_' . count($this->aItems),
            'value' => '',
            'name' => $sName,
        ];

        // слияние
        $aData = array_merge($aBase, $aData);

        // добавление в выходной массив
        $this->aItems[] = $aData;
    }

    /**
     * Добавляет js файл для модификации текущего набора полей.
     *
     * @param string $sFileName
     */
    public function setJSFieldUpdater($sFileName = 'FieldUpdater')
    {
        $this->addLibClass($sFileName);
        $this->sJSFileUpdater = $sFileName;
        $this->addComponent('ShowFieldUpdater');
    }

    /**
     * Отдает интерфейсный массив для атопостроителя интерфейсов.
     *
     * @return array
     */
    public function getInterfaceArray()
    {
        return [
            'items' => $this->aItems,
            'fieldUpdater' => $this->sJSFileUpdater,
        ];
    }

    /**
     * Отдает набор полей для вывода по умолчанию.
     *
     * @return string
     */
    protected function getDefaultFieldsSet()
    {
        return '';
    }
}
