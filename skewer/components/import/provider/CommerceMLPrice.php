<?php

namespace skewer\components\import\provider;

/**
 * Провайдер данных CommerceML для обновления цен
 * http://v8.1c.ru/edi/edi_stnd/131/offers.xml
 * Class CommerceML.
 */
class CommerceMLPrice extends CommerceMLPrototype
{
    /** @var string Путь к узлу товара */
    protected $goodXPath = '//КоммерческаяИнформация/ПакетПредложений/Предложения/Предложение';

    /** @var string Имя узла для списка полей */
    protected $fieldNodes = 'Цены';

    /** @var string Имя узла для поля */
    protected $fieldNode = 'Цена';

    /** @var string Имя узла для имени поля */
    protected $fieldName = 'ИдТипаЦены';

    /** @var string Имя узла для значения поля */
    protected $fieldValue = 'ЦенаЗаЕдиницу';

    /** @var array Массив типов цен */
    private $priceTypes = [];

    /** @var string Путь к типу цены */
    private $priceTypesXPath = '//КоммерческаяИнформация/ПакетПредложений/ТипыЦен/ТипЦены';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $this->priceTypes = $this->getConfigVal('priceTypes', []);

        if (!$this->priceTypes) {
            /* Получим список типов цен из файла */
            $this->getPriceTypes();
            $this->setConfigVal('priceTypes', $this->priceTypes);
        }
    }

    /**
     * Формируем список типов цен.
     */
    private function getPriceTypes()
    {
        $i = 0;
        while ($aPrices = $this->reader->getNode4XPathInLine($this->priceTypesXPath, $i)) {
            ++$i;
            $this->priceTypes[$aPrices['Ид']] = $aPrices['Наименование'];
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function makeGoodArray($aNode = [], $showName = false)
    {
        $aItem = [];

        if (!$aNode) {
            return [];
        }

        foreach ($aNode as $key => &$mValue) {
            // Обычные поля
            if (!is_array($mValue)) {
                $aItem[$key] = ($showName) ? $key . ':' . $mValue : $mValue;

            // Поля из списка
            } elseif ($key == $this->fieldNodes) {
                foreach ($mValue as &$aChildVal) {
                    foreach ($aChildVal as $aFieldKey => &$aFields) {
                        if ($aFieldKey == $this->fieldNode) {
                            foreach ($aFields as &$aField) {
                                if (isset($aField[$this->fieldName]) and isset($aField[$this->fieldValue])) {
                                    if (isset($this->priceTypes[$aField[$this->fieldName]])) {
                                        $aItem['field_' . $aField[$this->fieldName]] = ($showName) ? \Yii::t('import', 'price') . ' (' . $this->priceTypes[$aField[$this->fieldName]] . '): ' . $aField[$this->fieldValue] : $aField[$this->fieldValue];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $aItem;
    }
}
