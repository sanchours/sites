<?php

namespace skewer\components\import\field;

use skewer\build\Catalog\Collections\Search;
use skewer\build\Catalog\Collections\SeoElementCollection;
use skewer\components\catalog;
use skewer\components\seo\Api;
use skewer\components\seo\Service;
use skewer\helpers\Transliterate;
use yii\base\UserException;

/**
 * Обработчик поля типа коллекция.
 */
class DictCollection extends Dict
{
    /** ключ параметра первого id новой коллекции в конфиге*/
    const NEW_COLLECTIONS = 'new_collections';

    /** @var bool Создавать новые */
    protected $create = false;
    /** @var bool Активировать новый элемент коллекции */
    protected $active_new = false;
    /** @var bool Отправлять уведомление о добавлении элементов коллекции */
    protected $send_mail = true;

    protected static $parameters = [
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
     * Отдает значение на сохранение в запись товара.
     *
     * @throws UserException
     *
     * @return mixed
     */
    public function getValue()
    {
        $val = implode(',', $this->values);

        if ($this->sCardDictId) {
            //ищем в коллекции
            $aElement = catalog\Dict::getValByTitle($this->sCardDictId, $val, true);

            if ($aElement) {
                return $aElement['id'];
            }

            //создадим, если надо
            if ($this->create and $val != '') {
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
                    $oEntityRow = catalog\Entity::get($oItem->getModel()->getName());
                    $iCollectionId = $oEntityRow->id;

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

                    $aCollection = (catalog\Collection::getCollection($this->sCardDictId));
                    $this->logger->incParam('create_collection_element');
                    $this->logger->setListParam('create_collection_list', $aCollection->title . ': ' . $oItem->title);
                    if ($this->send_mail) {
                        $aNewCollections = $this->getTask()->getConfig()->getParam(self::NEW_COLLECTIONS);
                        if (!isset($aNewCollections[$this->sCardDictId]) || (isset($aNewCollections[$this->sCardDictId]) && $aNewCollections[$this->sCardDictId] == '')) {
                            $aNewCollections[$this->sCardDictId] = $oItem->id;
                        }
                        $this->getTask()->getConfig()->setParam(self::NEW_COLLECTIONS, $aNewCollections);
                    }

                    return $oItem->id;
                }
            }
        }

        return '';
    }
}
