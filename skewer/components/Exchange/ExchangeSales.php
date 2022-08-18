<?php

namespace skewer\components\Exchange;

use skewer\base\ft\Cache;
use skewer\base\orm\Query;
use skewer\base\SysVar;
use skewer\build\Adm\Order\Api;
use skewer\build\Adm\Order\ar\Goods;
use skewer\build\Adm\Order\ar\Order;
use skewer\build\Adm\Order\ar\OrderRow;
use skewer\build\Adm\Order\model\ChangeStatus;
use skewer\build\Adm\Order\model\Status;
use skewer\build\Adm\Order\Service;
use skewer\build\Page\Cart\OrderEntity;
use skewer\components\catalog\Card;
use skewer\components\forms\service\FormService;
use skewer\components\import\ar\ImportTemplate;
use skewer\components\import\ar\ImportTemplateRow;
use skewer\components\import\provider\XmlReader;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;

/**
 * Class ExchangeSales.
 *
 * @see http://v8.1c.ru/edi/edi_stnd/131/ - Протокол обмена между системой "1С:Предприятие" и сайтом
 */
class ExchangeSales extends ExchangePrototype
{
    /** Приём файла с заказами от 1с */
    protected function cmdFile()
    {
        if (($sFullNameFile = $this->loadFileImport()) !== false) {
            $sResponse = 'success';
        } else {
            $sResponse = 'failure';
        }

        // Если файл полностью загружен
        if (self::validateXmlFile($sFullNameFile)) {
            // Если стоит галочка "Обновлять заказы в Cms"
            if (SysVar::get('1c.updateStatusesCms')) {
                self::updateOrdersStatusFromFile($sFullNameFile);
            }

            // Удаляем ненужные файлы
            FileHelper::removeDirectory($this->getDirCurrentExchange());
        }

        $this->sendResponse($sResponse);
    }

    protected function cmdImport()
    {
        $this->sendResponse('success');
    }

    /** Вернёт файл с заказами (с сайта в 1с) */
    protected function cmdQuery()
    {
        $this->sendResponse(self::createOrdersCommerceMl());
    }

    /** 1c успешно принял и записал файл с заказами */
    protected function cmdSuccess()
    {
        $this->sendResponse('success');
    }

    /**
     * Формирует массив данных заказов.
     *
     * @param $aStatuses
     *
     * @return array|bool
     */
    private static function getOrdersData($aStatuses)
    {
        if (!$aStatuses) {
            return false;
        }

        $aOrders = Order::find()
            ->where('status', $aStatuses)
            ->getAll();

        if (!$aOrders) {
            return false;
        }

        $aDocuments = $aCommonContragent = $aContragents = [];

        // использовать общего контрагента?
        $bUseCommonContragent = (bool) SysVar::get('1c.useCommonContragent');

        // контрагенты заказов
        if ($bUseCommonContragent) {
            $aCommonContragent = self::getCommonContragent();
        } else {
            $aContragents = self::getContragents($aOrders);
        }

        // товары заказов
        $aGoods = self::getOrderGoods($aOrders);

        // реквизиты заказов
        $Properties = self::getProperiesOrders($aOrders);

        // Суммы заказов
        $aSumOrders = Api::getGoodsStatistic(ArrayHelper::getColumn($aOrders, 'id', []));

        /** @var OrderRow $oOrder */
        foreach ($aOrders as $oOrder) {
            $aDocuments[] = [
                'Документ' => [
                    'Ид' => $oOrder->id,
                    'Номер' => $oOrder->id,
                    'Дата' => date('Y-m-d', strtotime($oOrder->date)),
                    'ХозОперация' => 'Заказ товара',
                    'Роль' => 'Продавец',
                    'Валюта' => 'руб',
                    'Курс' => '1',
                    'Сумма' => $aSumOrders[$oOrder->id]['sum'],
                    'Контрагенты' => ($bUseCommonContragent) ? $aCommonContragent : $aContragents[$oOrder->id],
                    'Время' => date('H:i:s', strtotime($oOrder->date)),
                    'Комментарий' => self::buildOrderComment($oOrder),
                    'Товары' => $aGoods[$oOrder->id],
                    'ЗначенияРеквизитов' => $Properties[$oOrder->id],
                ],
            ];
        }

        return $aDocuments;
    }

    /**
     * Создать файл с заказами в формате CommerceMl.
     *
     * @return string
     */
    public static function createOrdersCommerceMl()
    {
        $oXml = new \XMLWriter();
        $oXml->openMemory();
        $oXml->startDocument('1.0', 'windows-1251');

        $aMainDocument = [
            'КоммерческаяИнформация' => [
                'attributes' => [
                    'ВерсияСхемы' => ArrayHelper::getValue(self::getCommerceMlSchemaVersions(), SysVar::get('1c.schema_version', 0)),
                    'ДатаФормирования' => date('Y-m-d\TH:i:s', time()),
                ],
            ],
        ];

        // Экспортируемые статусы
        $aExportStatuses = StringHelper::explode(SysVar::get('1c.export_statuses', ''), ',', true, true);

        // Документы заказов
        $aDocumentOrders = self::getOrdersData($aExportStatuses);

        if ($aDocumentOrders) {
            $aMainDocument['КоммерческаяИнформация'][] = $aDocumentOrders;
        }

        self::arrayToXml($oXml, $aMainDocument);

        return $oXml->outputMemory();
    }

    /**
     * Запишет массив в xml.
     *
     * [
     *  'Элемент1' => [
     *      'attributes' => [
     *          'имя_атрибута1' => 'значение1',
     *          'имя_атрибута2' => 'значение2'
     *      ],
     *      'элемент1_1' =>[...],
     *      'элемент1_2' =>[...]
     *       .....
     *       .....
     *       .....
     *  ]
     *
     * ]
     *
     * @param \XMLWriter $oXml
     * @param array $aData
     */
    private static function arrayToXml(\XMLWriter $oXml, $aData)
    {
        if (empty($aData) || !is_array($aData)) {
            return;
        }

        foreach ($aData as  $sKey => $mValue) {
            if (is_numeric($sKey)) {
                self::arrayToXml($oXml, $mValue);
            } elseif ($sKey == 'attributes') {
                foreach ($mValue as $sNameAttr => $sValueAttr) {
                    $oXml->writeAttribute($sNameAttr, $sValueAttr);
                }
            } else {
                if (is_array($mValue)) {
                    $oXml->startElement($sKey);
                    self::arrayToXml($oXml, $mValue);
                    $oXml->endElement();
                } else {
                    if ($sKey == 'value') {
                        $oXml->text($mValue);
                    } else {
                        $sKey = trim($sKey);
                        $oXml->writeElement($sKey, $mValue);
                    }
                }
            }
        }
    }

    /**
     * Вернёт массив вида [ 'id заказа' => 'данные контрагента'].
     *
     * @param $aOrders OrderRow[]
     *
     * @return array
     */
    private static function getContragents($aOrders)
    {
        $aContragents = [];

        foreach ($aOrders as $oOrder) {
            $aAgent = [
                'Контрагент' => [
                    'Наименование' => $oOrder->person,
                    'ПолноеНаименование' => $oOrder->person,
                    'Роль' => 'Покупатель',
                    'Контакты' => [
                        [
                            'Контакт' => [
                                'Тип' => 'ТелефонРабочий',
                                'Значение' => $oOrder->phone,
                            ],
                        ],
                        [
                            'Контакт' => [
                                'Тип' => 'Почта',
                                'Значение' => $oOrder->mail,
                            ],
                        ],
                    ],
                ],
            ];

            $aContragents[$oOrder->id] = $aAgent;
        }

        return $aContragents;
    }

    /**
     * Вернет товары заказов, массив вида [ 'id_заказа' => [ 'товар1', 'товар2', ... ] ].
     *
     * @param $aOrders OrderRow[]
     *
     * @return array
     */
    private static function getOrderGoods($aOrders)
    {
        // товары
        $aOut = [];

        // список id заказов
        $aListOrdersId = ArrayHelper::getColumn($aOrders, 'id', []);

        // товары заказов
        $aOrderGoods = Goods::find()
            ->where('id_order', $aListOrdersId)
            ->asArray()
            ->getAll();

        $aListGoodsId = ArrayHelper::getColumn($aOrderGoods, 'id_goods', []);

        $aGoodsData = self::getGoodsData($aListGoodsId);

        $sNameUniqueField = self::getUniqueFieldInImportTemplate();

        foreach ($aOrderGoods as $aGood) {
            $iCurrentGoodId = $aGood['id_goods'];

            $aCurrentGoodData = [
                'Товар' => [
                    'Ид' => ArrayHelper::getValue($aGoodsData, "{$iCurrentGoodId}.{$sNameUniqueField}", ''),
                    'Артикул' => ArrayHelper::getValue($aGoodsData, "{$iCurrentGoodId}.article", ''),
                    'Наименование' => $aGood['title'],
                    'БазоваяЕдиница' => [
                        'attributes' => [
                            'Код' => 796,
                            'НаименованиеПолное' => 'Штука',
                            'МеждународноеСокращение' => 'PCE',
                        ],
                        'value' => 'шт',
                    ],
//                        'СтавкиНалогов' => [
//                            'СтавкаНалога' => [
//                                'Наименование' => 'НДС',
//                                'Ставка' => 18
//                            ]
//                        ],
                    'ЗначенияРеквизитов' => [
                        [
                            'ЗначениеРеквизита' => [
                                'Наименование' => 'ВидНоменклатуры',
                                'Значение' => 'Товар',
                            ],
                        ],
                        [
                            'ЗначениеРеквизита' => [
                                'Наименование' => 'ТипНоменклатуры',
                                'Значение' => 'Товар',
                            ],
                        ],
                    ],
                    'ЦенаЗаЕдиницу' => $aGood['price'],
                    'Количество' => $aGood['count'],
                    'Сумма' => $aGood['total'],
//                    'Единица'       => 'шт',
//                    'Коэффициент'   => 1,
//                        'Налоги' => [
//                            'Налог' => [
//                                'Наименование' => 'НДС',
//                                'УчтеноВСумме' => 'true',
//                                'Сумма'        => 120.43,
//                                'Ставка'       => 18
//                            ]
//
//                        ]
                ],
            ];

            $aOut[$aGood['id_order']][] = $aCurrentGoodData;
        }

        return $aOut;
    }

    /**
     * @param $aOrders OrderRow[]
     *
     * @return array
     */
    private static function getProperiesOrders($aOrders)
    {
        $aOut = [];

        $aTypePayment = \skewer\build\Tool\DeliveryPayment\models\TypePayment::find()
            ->indexBy('id')
            ->asArray()
            ->all();

        // Заголовки статусов
        $aTitleStatuses = ArrayHelper::map(Status::getList(), 'id', 'title');

        foreach ($aOrders as $oOrder) {
            $aChangeStatuses = self::getListChangeStatuses($oOrder->id, true);

            $aOut[$oOrder->id] = [
                [
                    'ЗначениеРеквизита' => [
                        'Наименование' => 'Статус заказа',
                        'Значение' => $aTitleStatuses[$oOrder->status],
                    ],
                ],
                [
                    'ЗначениеРеквизита' => [
                        'Наименование' => 'Номер заказа на сайте',
                        'Значение' => $oOrder->id,
                    ],
                ],
                [
                    'ЗначениеРеквизита' => [
                        'Наименование' => 'Дата заказа на сайте',
                        'Значение' => date('Y-m-d H:i:s', strtotime($oOrder->date)),
                    ],
                ],
                [
                    'ЗначениеРеквизита' => [
                        'Наименование' => 'Заказ оплачен',
                        'Значение' => in_array(Status::getIdByPaid(), $aChangeStatuses) ? 'true' : 'false',
                    ],
                ],
                [
                    'ЗначениеРеквизита' => [
                        'Наименование' => 'Финальный статус',
                        'Значение' => in_array(Status::getIdByClose(), $aChangeStatuses) ? 'true' : 'false',
                    ],
                ],
                [
                    'ЗначениеРеквизита' => [
                        'Наименование' => 'Отменен',
                        'Значение' => in_array(Status::getIdByCancel(), $aChangeStatuses) ? 'true' : 'false',
                    ],
                ],
                [
                    'ЗначениеРеквизита' => [
                        'Наименование' => 'Доставка разрешена',
                        'Значение' => in_array(Status::getIdBySend(), $aChangeStatuses) ? 'true' : 'false',
                    ],
                ],
            ];

            if (in_array('tp_pay', self::getExistFieldForm())) {
                $aOut[$oOrder->id][] = [
                    'ЗначениеРеквизита' => [
                        'Наименование' => 'Метод оплаты',
                        'Значение' => $aTypePayment[$oOrder->type_payment]['title'],
                    ],
                ];
            }
        }

        return $aOut;
    }

    /**
     * Обновить статусы заказов информацией из файла.
     *
     * @param $sFilePath string -  путь к файлу
     */
    private static function updateOrdersStatusFromFile($sFilePath)
    {
        $oDom = new \DOMDocument();
        $oDom->load($sFilePath);

        $xpath = new \DOMXPath($oDom);
        $aEntries = XmlReader::queryXPath($xpath, '//КоммерческаяИнформация/Документ');

        $i = 0;
        while ($item = $aEntries->item($i++)) {
            $oSimpleXml = simplexml_import_dom($item);

            $iCurrentOrderId = $oSimpleXml->Hoмep;

            /** @var OrderRow $oOrder */
            if ($oOrder = Order::findOne(['id' => $iCurrentOrderId])) {
                $bIsPaid = $bIsSenden = false;

                foreach ($oSimpleXml->ЗнaчeнияPeквизитoв->ЗнaчeниePeквизитa as $item) {
                    // Заказ оплачен?
                    if (in_array($item->Haимeнoвaниe, ['Номер оплаты по 1С', 'Дата оплаты по 1С'])) {
                        $bIsPaid = true;
                    }

                    // Заказ отгружен?
                    if (in_array($item->Haимeнoвaниe, ['Дата отгрузки по 1С', 'Номер отгрузки по 1С'])) {
                        $bIsSenden = true;
                    }
                }

                if ($bIsPaid || $bIsSenden) {
                    $iNewStatus = false;
                    $iOldStatus = $oOrder->status;

                    if ($bIsPaid && $bIsSenden) {
                        $iNewStatus = SysVar::get('1c.status_after_delivery_and_paid');
                    } else {
                        $iNewStatus = ($bIsPaid)
                            ? SysVar::get('1c.status_after_paid')
                            : SysVar::get('1c.status_after_delivery');
                    }

                    if ($iNewStatus !== false && ($iOldStatus != $iNewStatus)) {
                        $oOrder->status = $iNewStatus;
                        $oOrder->save();
                        Service::sendMailChangeOrderStatus($oOrder->id, $iOldStatus, $iNewStatus);
                    }
                }
            }
        }
    }

    /**
     * Вернёт общего контрагента.
     *
     * @return array
     */
    private static function getCommonContragent()
    {
        return [
            'Контрагент' => [
                'Наименование' => 'Web-caйт',
                'ПолноеНаименование' => 'Web-caйт',
                'Роль' => 'Покупатель',
            ],
        ];
    }

    /**
     * Построит комментарий заказа.
     *
     * @param $oOrder OrderRow - заказ
     *
     * @return string
     */
    public static function buildOrderComment($oOrder)
    {
        $aOrderFields = Order::getModel()->getColumnSet('mail');

        $aDataOrder = $oOrder->getDataOrder($aOrderFields);

        $aTitleToValue = [];

        foreach ($aDataOrder as $sFieldName => $sValue) {
            if (!$sValue['value']) {
                continue;
            }
            $aTitleToValue[$sFieldName] = sprintf('%s: %s', $sValue['title'], $sValue['value']);
        }

        $sOrderComment = implode("\r\n", $aTitleToValue);

        return $sOrderComment;
    }

    /**
     * Версии схем CommerceML.
     *
     * @return array
     */
    public static function getCommerceMlSchemaVersions()
    {
        return [
            '2.05',
            '2.07',
        ];
    }

    /**
     * Вернёт все статусы в которых когда-либо находился заказ.
     *
     * @param int $iOrderId - ид заказа
     * @param bool $bWithStartStatus - включить стартовый статус(новый)?
     *
     * @return array
     */
    private static function getListChangeStatuses($iOrderId, $bWithStartStatus = true)
    {
        $aChangeStatuses = ChangeStatus::find()
            ->where(['id_order' => $iOrderId])
            ->asArray()->all();

        $aChangeStatuses = ArrayHelper::getColumn($aChangeStatuses, 'id_new_status');

        if ($bWithStartStatus) {
            array_merge($aChangeStatuses, [Status::getIdByNew()]);
        }

        return array_unique($aChangeStatuses);
    }

    /**
     * Проверяет xml-файл на валидность. Функция используется для проверки полной загрузки файла.
     *
     * @param string $sFilePath - путь к файлу
     *
     * @return bool
     */
    private static function validateXmlFile($sFilePath)
    {
        $xml = new \DOMDocument();
        $result = @$xml->load($sFilePath);

        if ($result === false) {
            return false;
        }

        return true;
    }

    /**
     * Вернёт имя поля карточки, заданного в шаблоне импорта в качестве уникального.
     *
     * @return mixed
     */
    private static function getUniqueFieldInImportTemplate()
    {
        $iIdImportTemplate = SysVar::get('1c.id_import_template_goods');

        /** @var ImportTemplateRow $oImportTemplate */
        $oImportTemplate = ImportTemplate::findOne(['id' => $iIdImportTemplate]);
        $aSettings = json_decode($oImportTemplate->settings, true);

        // имя поля => тип поля
        $aFields = ArrayHelper::map($aSettings['fields'], 'name', 'type');

        return array_search('Unique', $aFields, true);
    }

    /**
     * Вернет данные товаров по списку id товаров.
     *
     * @param $aListGoodsId
     *
     * @return array
     */
    private static function getGoodsData($aListGoodsId)
    {
        $aCardList = [];

        $iIdImportTemplate = SysVar::get('1c.id_import_template_goods');
        /** @var ImportTemplateRow $oImportTemplate */
        $oImportTemplate = ImportTemplate::findOne(['id' => $iIdImportTemplate]);
        $aCardList[] = $oImportTemplate->card;

        array_push($aCardList, Card::DEF_BASE_CARD);

        $aGoodsData = [];
        foreach ($aCardList as $item) {
            $oExtModel = Cache::get($item);
            $aData = Query::SelectFrom($oExtModel->getTableName(), $oExtModel)
                ->where($oExtModel->getPrimaryKey(), $aListGoodsId)
                ->index($oExtModel->getPrimaryKey())
                ->asArray()->getAll();

            // данные из базовой карточки перезапишут данные из расширенных карточек по одноименным полям
            foreach ($aData as $idGood => $aDataGood) {
                if (isset($aGoodsData[$idGood])) {
                    $aGoodsData[$idGood] = array_merge($aGoodsData[$idGood], $aDataGood);
                } else {
                    $aGoodsData[$idGood] = $aDataGood;
                }
            }
        }

        return $aGoodsData;
    }

    public static function getExistFieldForm()
    {
        $formAggregate = (new FormService())
            ->getFormByName(OrderEntity::tableName());
        $keysFields = array_keys($formAggregate->fields());

        if (in_array('name', $keysFields)) {
            $keysFields[] = 'person';
        }

        if (in_array('email', $keysFields)) {
            $keysFields[] = 'mail';
        }

        return $keysFields;
    }
}
