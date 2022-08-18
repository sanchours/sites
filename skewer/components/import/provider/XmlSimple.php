<?php

namespace skewer\components\import\provider;

use skewer\components\import;

/**
 * Провайдер для Xml простого типа
 * Class XmlSimple.
 */
class XmlSimple extends Prototype
{
    /** @var XmlReader xml-reader */
    protected $reader;

    /** @var string XPath Путь к узлу товара */
    protected $XPath = '';

    /** @var int Счетчик прочитанных узлов */
    protected $row = 0;

    protected $parameters = [
        'XPath' => [
            'title' => 'field_xml_simple_xpath',
            'datatype' => 's',
            'viewtype' => 'select',
            'default' => '',
            'method' => 'getXPathList',
        ],
    ];

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getAllowedExtension()
    {
        return ['xml'];
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        try {
            $this->reader = new XmlReader($this->file, $this->codding != import\Api::utf);
        } catch (\Exception $e) {
            $this->fail(\Yii::t('import', 'error_xml_read'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beforeExecute()
    {
        if (!$this->XPath) {
            $this->fail(\Yii::t('import', 'error_not_found_xpath'));
        }

        $this->row = (int) $this->getConfigVal('row', 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getRow()
    {
        /* Читаем узел в массив */
        try {
            $aRes = $this->reader->getNode4XPathInLine($this->XPath, $this->row);
            $aData = $this->formatAttributes($aRes);
        } catch (\Exception $e) {
            throw new \Exception(\Yii::t('import', 'error_not_valid_xpath'), 0, $e);
        }

        ++$this->row;
        $this->setConfigVal('row', $this->row);

        /* Нет данных */
        if (!$aData) {
            return false;
        }

        return $aData;
    }

    /**
     * Рекурсивно собирает значения атрибутов из дочерних узлов и выводит по ключам в массив на первый уровень.
     *
     * @param $aData
     *
     * @return array
     */
    public function formatAttributes($aData)
    {
        $aItems = [];
        if (is_array($aData)) {
            foreach ($aData as $key => $mValue) {
                if (!is_array($mValue)) {
                    $aItems[$key] = $mValue;
                } else {
                    $aItems = array_merge($aItems, $this->formatAttributes($mValue));
                }
            }
        }

        return $aItems;
    }

    /**
     * {@inheritdoc}
     */
    public function getExample()
    {
        try {
            if (!$this->XPath) {
                /* Текстовый кусок */
                return '<plaintext>' . $this->encode($this->reader->getExampleText());
            }
            /* Пример товара */
            return '<plaintext>' . $this->reader->getFirstElement4XPathToText($this->XPath);
        } catch (\Exception $e) {
            $this->fail(\Yii::t('import', 'error_not_valid_xpath'));
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getInfoRow()
    {
        if (!$this->XPath) {
            $this->fail(\Yii::t('import', 'error_not_found_xpath'));
        }

        $aRes = [];
        try {
            $aItems = $this->reader->getElementsListFull($this->XPath, false);
        } catch (\Exception $e) {
            throw new \Exception(\Yii::t('import', 'error_not_valid_xpath'), 0, $e);
        }

        foreach ($aItems as $key => $aItem) {
            $app = ($aItem['attr']) ? \Yii::t('import', 'xml_attr') : '';
            $aRes[$key] = $key . $app . ':' . $aItem['value'];
        }

        return $aRes;
    }

    /**
     * {@inheritdoc}
     */
    public function getPureString()
    {
        return $this->reader->getFirstText();
    }

    /**
     * Список узлов.
     *
     * @return array
     */
    public function getXPathList()
    {
        return $this->reader->getXPathList();
    }

    protected function checkCoding()
    {
        if (mb_strtolower($this->reader->getDeclarateCodding()) != $this->codding) {
            $this->fail(\Yii::t('import', 'error_codding'));
        }
    }
}
