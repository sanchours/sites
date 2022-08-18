<?php

namespace skewer\components\import\provider;

/**
 * Провайдер данных для yml-формата
 * Описание формата https://yandex.ru/support/partnermarket/yml/about-yml.html
 * Class Yml.
 */
class Yml extends CommerceMLPrototype
{
    /** @var string Путь к узлу товара */
    protected $goodXPath = '//yml_catalog/shop/offers/offer';

    /** @var array Список разделов */
    protected $sectionList = [];

    /** @var string XPath для списка разделов */
    protected $sectionListPath = '//yml_catalog/shop/categories';

    /** @var string Имя ноды с разделами */
    protected $sectionNode = 'categoryId';

    /** @var string Имя ноды с картинками */
    protected $photoNode = 'picture';
    /** @var string Имя ноды с картинками */
    protected $paramNode = 'param';

    /** @var string Разделитель фоток */
    protected $photosDelimiter = ',';

    /**
     * {@inheritdoc}
     */
    public function beforeExecute()
    {
        parent::beforeExecute();

        $this->sectionList = $this->getConfigVal('sectionList', []);

        if (!$this->sectionList) {
            /* Получим список разделов из файла */
            $this->getSectionList();
            $this->setConfigVal('sectionList', $this->sectionList);
        }
    }

    /**
     * Добавление в список разделов раздела.
     *
     * @param $aNodeList
     */
    private function addSection($aNodeList)
    {
        if (is_array($aNodeList)) {
            foreach ($aNodeList as $aNodes) {
                if (isset($aNodes['category/id'][0], $aNodes['category/value'][0])) {
                    $title = $aNodes['category/value'][0];
                    if (isset($aNodes['category/parentId'][0], $this->sectionList[$aNodes['category/parentId'][0]])) {
                        $this->sectionList[$aNodes['category/id'][0]] = $this->sectionList[$aNodes['category/parentId'][0]] . '/' . $title;
                    } else {
                        $this->sectionList[$aNodes['category/id'][0]] = $title;
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
        $this->addSection($aGroups['categories/category']);
    }

    public function getExample()
    {
        return parent::getExample();
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
            // Разделы. идет первым т.к поле с разделом такое же как и обычные поля
            if ($key == $this->sectionNode) {
                if (isset($this->sectionList[trim($mValue)])) {
                    $aItem[self::group] = $this->sectionList[trim($mValue)];
                }
            }
            // Обычные поля
            elseif (!is_array($mValue)) {
                $aItem[$key] = ($showName) ? $key . ':' . $mValue : $mValue;
            // обработка группы полей param
            } elseif ($key == $this->paramNode) {
                foreach ($mValue as $param) {
                    if (isset($param[$key . '/value']) && $param[$key . '/value']) {
                        $aItem[$this->paramNode][] = $param[$key . '/name'] . \Yii::t('import', 'yml_text_delimiter') . $param[$key . '/value'];
                        $aItem[$param[$key . '/name']] = ($showName) ? $param[$key . '/name'] . ':' . $param[$key . '/value'] : $param[$key . '/value'];
                    }
                }
                $aItem[$this->paramNode] = ($showName) ? $this->paramNode . ': ' . implode('&@&', $aItem[$this->paramNode]) : implode('&@&', $aItem[$this->paramNode]);
            // Обработка массива картинок. Если картинка одна, то сработает первое условие
            } elseif ($key == $this->photoNode) {
                $sValue = implode($this->photosDelimiter, $mValue);
                $aItem[$key] = ($showName) ? $key . ':' . $sValue : $sValue;
            }
        }

        return $aItem;
    }
}
