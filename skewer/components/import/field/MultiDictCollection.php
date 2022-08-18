<?php

namespace skewer\components\import\field;

use skewer\build\Catalog\Collections\Search;
use skewer\build\Catalog\Collections\SeoElementCollection;
use skewer\components\catalog;
use skewer\components\seo\Api;
use skewer\components\seo\Service;
use skewer\helpers\Transliterate;
use yii\base\UserException;

class MultiDictCollection extends DictCollection
{
    protected static $parameters = [
        'delimiterMultiDict' => [
            'title' => 'field_multiCollection_delimiter',
            'datatype' => 's',
            'viewtype' => 'string',
            'default' => ',',
        ],
        'create' => [
            'title' => 'field_dict_create',
            'datatype' => 'i',
            'viewtype' => 'check',
            'default' => 0,
        ],
        'active_new' => [
            'title' => 'field_el_col_new',
            'datatype' => 'i',
            'viewtype' => 'check',
            'default' => 0,
        ],
        'send_mail' => [
            'title' => 'field_col_send_mail',
            'datatype' => 'i',
            'viewtype' => 'check',
            'default' => 1,
        ],
    ];

    /**
     * @var string разделитель для мультисправочника
     */
    protected $delimiterMultiDict = ',';

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

                //ищем в коллекции
                $aElement = $this->getFromCache($val);

                //Если нашли запись в коллекции, то добавляем её id в итоговый массив,
                //если нет, то добавляем запись в коллекцию и добавляем её id в итоговый массив
                if ($aElement) {
                    $aIds[] = $aElement['id'];
                } elseif ($this->create and $val != '') {
                    $alias = Transliterate::generateAlias($val);
                    $iSectionId = catalog\Section::get4Collection(catalog\Card::getId($this->sCardDictId));

                    $oTableDict = catalog\Dict::getTableDict($this->sCardDictId);
                    $oItem = $oTableDict->getNewRow();

                    $aData = [
                        'title' => $val,
                        'alias' => $alias,
                        'active' => $this->active_new,
                        'on_main' => '0',
                    ];

                    $oItem->setData($aData);
                    $oItem->save();

                    if ($oItem->id) {
                        $alias = Service::generateAlias($alias, $oItem->id, $iSectionId, 'CollectionViewer_' . $oItem->getModel()->getName());
                        $oItem->alias = $alias;
                        $oItem->save();

                        $aCollection = (catalog\Collection::getCollection($this->sCardDictId));
                        $iCollectionId = $aCollection->id;

                        Api::saveJSData(
                            new SeoElementCollection($oItem->id, $iCollectionId, [], $oItem->getModel()->getName()),
                            new SeoElementCollection($oItem->id, $iCollectionId, $oItem->getData(), $oItem->getModel()->getName()),
                            $aData,
                            0,
                            false
                        );

                        $oSearch = new Search();
                        $oSearch->setCard($oTableDict->getName());
                        $oSearch->updateByObjectId($oItem->id);

                        $this->logger->incParam('create_collection_element');
                        $this->logger->setListParam('create_collection_list', $aCollection->title . ': ' . $oItem->title);
                        if ($this->send_mail) {
                            $aNewCollections = $this->getTask()->getConfig()->getParam(self::NEW_COLLECTIONS);
                            if (!isset($aNewCollections[$this->sCardDictId]) || (isset($aNewCollections[$this->sCardDictId]) && $aNewCollections[$this->sCardDictId] == '')) {
                                $aNewCollections[$this->sCardDictId] = $oItem->id;
                            }
                            $this->getTask()->getConfig()->setParam(self::NEW_COLLECTIONS, $aNewCollections);
                        }
                        $aIds[] = $oItem->id;
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
