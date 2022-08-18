<?php

namespace skewer\components\import\provider;

use skewer\components\import;

/**
 * Прототип провайдера данных CommerceML
 * Описание стандарта http://v8.1c.ru/edi/edi_stnd/90/92.htm
 * Class CommerceML.
 */
abstract class CommerceMLPrototype extends Prototype
{
    /** Индекс для разделов */
    const group = 'group';

    /** @var XmlReader xml-reader */
    protected $reader;

    /** @var int Счетчик прочитанных узлов */
    protected $row = 0;

    /** @var string Путь к узлу товара */
    protected $goodXPath = '';

    /** @var string Имя узла для списка полей */
    protected $fieldNodes = '';

    /** @var string Имя узла для поля */
    protected $fieldNode = '';

    /** @var string Имя узла для имени поля */
    protected $fieldName = '';

    /** @var string Имя узла для значения поля */
    protected $fieldValue = '';

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
        $this->reader = new XmlReader($this->file, $this->codding != import\Api::utf);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeExecute()
    {
        $this->row = (int) $this->getConfigVal('row', 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getRow()
    {
        /** Читаем узел в массив */
        $aData = $this->makeGoodArray($this->reader->getNode4XPathInLine($this->goodXPath, $this->row));

        ++$this->row;
        $this->setConfigVal('row', $this->row);

        /* Нет данных */
        if (!$aData) {
            return false;
        }

        return $aData;
    }

    /**
     * {@inheritdoc}
     */
    public function getExample()
    {
        /*
         * Для таких выгрузок не будем выдавать портянку из xml, а выдадим только нужную инфу
         */
        return implode('<br>', $this->makeGoodArray($this->reader->getNode4XPathInLine($this->goodXPath, 0), true));
    }

    /**
     * {@inheritdoc}
     */
    public function getInfoRow()
    {
        $aRes = $this->makeGoodArray($this->reader->getNode4XPathInLine($this->goodXPath, 0), true);

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
     * Убирает лишние переносы для вывода текста в читаемом виде.
     *
     * @param $text
     *
     * @return mixed
     */
    protected function formatText($text)
    {
        /** special for 1c format */
        $text = preg_replace("/[\r\n]?\t{2,}[\r\n]/i", '', $text);
        $text = preg_replace("/[\r\n]{2,}/i", '$1', $text);

        return $text;
    }

    /**
     * Переводим многомерный массив по узлу товара в одномерный массив полей.
     *
     * @param array $aNode
     * @param bool $showName - выводить с именами полей
     *
     * @return array
     */
    abstract protected function makeGoodArray($aNode = [], $showName = false);
}
