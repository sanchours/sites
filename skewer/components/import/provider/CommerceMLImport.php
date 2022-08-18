<?php

namespace skewer\components\import\provider;

use skewer\base\section\Api;
use skewer\components\import;

/**
 * Провайдер данных CommerceML для импорта товаров
 * Пример http://v8.1c.ru/edi/edi_stnd/131/import.xml
 * Class CommerceML.
 */
class CommerceMLImport extends CommerceMLPrototype
{
    /** @var string Путь к узлу товара */
    protected $goodXPath = '//КоммерческаяИнформация/Каталог/Товары/Товар';

    /** @var string Имя узла для списка полей */
    protected $fieldNodes = 'ЗначенияРеквизитов';

    /** @var string Имя узла для поля */
    protected $fieldNode = 'ЗначениеРеквизита';

    /** @var string Имя узла для имени поля */
    protected $fieldName = 'Наименование';

    /** @var string Имя узла для значения поля */
    protected $fieldValue = 'Значение';

    /** @var array Список разделов */
    protected $sectionList = [];

    /** @var string XPath для списка разделов */
    protected $sectionListPath = '//КоммерческаяИнформация/Классификатор/Группы';

    /** @var string Имя ноды с разделами */
    protected $sectionNode = 'Группы';

    /** @var string Имя ноды с картинками */
    protected $photoNode = 'Картинка';

    /** @var string Разделитель фоток */
    protected $photosDelimiter = ',';

    /** @var array Список справочников */
    protected $dictList = [];

    /** @var string Узел со значениями справочников */
    protected $dictNode = 'ЗначенияСвойств';

    /** @var string XPath для списка справочников */
    protected $dictListPath = '//КоммерческаяИнформация/Классификатор/Свойства/Свойство';

    /**
     * {@inheritdoc}
     */
    public function beforeExecute()
    {
        parent::beforeExecute();

        $this->sectionList = $this->getConfigVal('sectionList', []);
        $this->dictList = $this->getConfigVal('dictList', []);

        if (!$this->sectionList) {
            /* Получим список разделов из файла */
            $this->getSectionList();
            $this->setConfigVal('sectionList', $this->sectionList);
        }

        if (!$this->dictList) {
            /* Получим список справочников из файла */
            $this->getDictList();
            $this->setConfigVal('dictList', $this->dictList);
        }
    }

    /**
     * Добавление в список разделов раздела.
     *
     * @param $aNodeList
     * @param $sParent
     */
    private function addSection($aNodeList, $sParent)
    {
        if (is_array($aNodeList)) {
            foreach ($aNodeList as $aNodes) {
                $title = $sParent;
                if (isset($aNodes['Группа/Ид'][0], $aNodes['Группа/Наименование'][0])) {
                    $title .= Api::$sDelimiter . $aNodes['Группа/Наименование'][0];
                    $title = trim($title, ' ' . Api::$sDelimiter);

                    $this->sectionList[$aNodes['Группа/Ид'][0]] = $title;
                }

                if (isset($aNodes['Группа/Группы']) && is_array($aNodes['Группа/Группы'])) {
                    foreach ($aNodes['Группа/Группы'] as $aNode) {
                        if (isset($aNode['Группы/Группа'])) {
                            $this->addSection($aNode['Группы/Группа'], $title);
                        }
                    }
                }
            }
        }
    }

    /**
     * Формируем список разделов.
     */
    private function getSectionList()
    {
        $aGroups = $this->reader->getNode4XPath($this->sectionListPath);
        $this->addSection($aGroups['Группы/Группа'], '');
    }

    public function getExample()
    {
        $this->getDictList();

        return parent::getExample();
    }

    /**
     * Формируем список справочников.
     */
    private function getDictList()
    {
        $i = 0;
        while ($aDicts = $this->reader->getNode4XPath($this->dictListPath, $i)) {
            ++$i;
            $dict = [];
            $dict['id'] = $aDicts['Свойство/Ид'][0];
            $dict['title'] = $aDicts['Свойство/Наименование'][0];
            if (isset($aDicts['Свойство/ВариантыЗначений']) && is_array($aDicts['Свойство/ВариантыЗначений'])) {
                foreach ($aDicts['Свойство/ВариантыЗначений'] as $aValues) {
                    if (isset($aValues['ВариантыЗначений/Справочник']) && is_array($aValues['ВариантыЗначений/Справочник'])) {
                        foreach ($aValues['ВариантыЗначений/Справочник'] as $aParam) {
                            if (isset($aParam['Справочник/ИдЗначения'][0], $aParam['Справочник/Значение'][0])) {
                                $dict['items'][$aParam['Справочник/ИдЗначения'][0]] = $aParam['Справочник/Значение'][0];
                            }
                        }
                    }
                }
            }

            $this->dictList[$dict['id']] = $dict;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getInfoRow()
    {
        $aRes = parent::getInfoRow();
        $aRes[self::group] = \Yii::t('import', 'section');

        return $aRes;
    }

    /**
     * {@inheritdoc}
     */
    protected function makeGoodArray($aNode = [], $showName = false)
    {
        if (!$aNode) {
            return [];
        }

        $aItem = [];

        foreach ($aNode as $key => &$mValue) {
            // Обычные поля
            if (!is_array($mValue)) {
                $aItem[$key] = ($showName) ? $key . ':' . $mValue : $mValue;

            // Обработка массива картинок. Если картинка одна, то сработает первое условие
            } elseif ($key == $this->photoNode) {
                $sValue = implode($this->photosDelimiter, $mValue);
                $aItem[$key] = ($showName) ? $key . ':' . $sValue : $sValue;

            // Разделы.
            } elseif ($key == $this->sectionNode) {
                $aChildVal = reset($mValue);
                if (isset($aChildVal['Ид']) and isset($this->sectionList[$aChildVal['Ид']])) {
                    $aItem[self::group] = $this->sectionList[$aChildVal['Ид']];
                }

                // Поля из списка
            } elseif ($key == $this->fieldNodes) {
                foreach ($mValue as &$aChildVal) {
                    foreach ($aChildVal as $aFieldKey => &$aFields) {
                        if ($aFieldKey == $this->fieldNode) {
                            foreach ($aFields as &$aField) {
                                if (isset($aField[$this->fieldName]) and isset($aField[$this->fieldValue])) {
                                    $aItem['field_' . $aField[$this->fieldName]] = ($showName) ? $aField[$this->fieldName] . ': ' . $aField[$this->fieldValue] : $aField[$this->fieldValue];
                                }
                            }
                        }
                    }
                }

                // Справочники
            } elseif ($key == $this->dictNode) {
                foreach ($mValue as &$aChildVal) {
                    foreach ($aChildVal['ЗначенияСвойства'] as &$aField) {
                        if (isset($aField['Ид']) and isset($this->dictList[$aField['Ид']])) {
                            $id = $aField['Ид'];
                            $val = $aField['Значение'];
                            $valItem = isset($this->dictList[$id]['items'][$val]) ? $this->dictList[$id]['items'][$val] : $val;
                            $aItem['dict_' . $this->dictList[$id]['id']] = ($showName) ? $this->dictList[$id]['title'] . ':' . $valItem : $valItem;
                        }
                    }
                }
            }
        }

        return $aItem;
    }
}
