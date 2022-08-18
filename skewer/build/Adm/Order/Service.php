<?php
/**
 * @project Skewer
 *
 * @author kolesnikov,max $Author: $
 *
 * @version $Revision:  $
 * @date $Date: $
 */

namespace skewer\build\Adm\Order;

use skewer\base\orm\ActiveRecord;
use skewer\base\site\ServicePrototype;
use skewer\base\site\Site;
use skewer\base\site_module\Parser;
use skewer\build\Adm\Order as AdmOrder;
use skewer\build\Adm\Order\model\ChangeStatus;
use skewer\build\Adm\Order\model\Status;
use skewer\build\Page\Auth\Api as ApiAuth;
use skewer\build\Page\Cart as Cart;
use skewer\build\Tool\DeliveryPayment\models\TypePayment;
use skewer\build\Tool\Payments;
use skewer\components\cart\models;
use skewer\components\catalog;
use skewer\components\forms;
use skewer\components\i18n\ModulesParams;
use skewer\helpers\Mailer;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

/**
 * Class Service.
 */
class Service extends ServicePrototype
{
    private static $aStatus = [];

    protected static function buildArrayStatus()
    {
        static::$aStatus = ArrayHelper::map(Status::getList(false, \Yii::$app->language), 'id', 'title');
    }

    /**
     * Список статусов.
     *
     * @return array
     */
    public static function getStatusList()
    {
        if (empty(static::$aStatus)) {
            static::buildArrayStatus();
        }

        return static::$aStatus;
    }

    /**
     * Метод отправки письма.
     *
     * @param string $sMail email кому отправляем
     * @param string $sTitle заголовок письма
     * @param string $sBody текст письма
     * @param array $aOptions массив меток для автозамены
     *
     * @return bool
     */
    public static function sendMail($sMail, $sTitle, $sBody, $aOptions = [])
    {
        if (isset($aOptions['token'])) {
            $aOptions['link'] = '<a href="' . Site::httpDomain() . ApiAuth::getProfilePath() . '?cmd=detail&token=' . $aOptions['token'] . '">' . \Yii::t('adm', 'by_link') . '</a>';
        }

        return $oMailer = Mailer::sendMail($sMail, $sTitle, $sBody, $aOptions);
    }

    /**
     * Заголовок статуса (для виджета).
     *
     * @param ActiveRecord $oItem
     * @param $sField
     *
     * @return string
     */
    public static function getStatusValue(
        $oItem,
        /* @noinspection PhpUnusedParameterInspection */
        $sField
    ) {
        $aStatulList = static::getStatusList();

        if (isset($aStatulList[$oItem['status']])) {
            return $aStatulList[$oItem['status']];
        }

        return '';
    }

    /**
     * Письмо о смене статуса.
     *
     * @param int $iOrderId
     * @param int $iBeforeStatus
     * @param int $iAfterStatus
     */
    public static function sendMailChangeOrderStatus($iOrderId, $iBeforeStatus, $iAfterStatus)
    {
        $aStatus = Status::getListTitle();

        /**
         * @var ar\OrderRow
         */
        $oRow = ar\Order::find($iOrderId);

        // тут надо отправить email о смене статуса, сделал в лоб, скорее всего надо будет переписать
        if (isset($aStatus[$oRow->status]) || $aStatus[$iAfterStatus]) {
            $oStatus = Status::findOne(['id' => $iAfterStatus]);

            //Если есть привязка статуса к методу в модуле Payment, то стараемся выполнить.
            /** @var TypePayment $oPayment */
            $oPayment = ($oRow->type_payment) ? TypePayment::findOne(['id' => $oRow->type_payment]) : false;
            if ($oPayment) {
                if ($oStatus === null) {
                    throw new Exception('Invalid status');
                }
                $statusName = $oStatus->name;

                $oPaymentModule = Payments\Api::make($oPayment->payment);
                if (method_exists($oPaymentModule, 'bindedStatusMethod') == true and method_exists($oPaymentModule, 'executeStatusMethod') == true) {
                    $oPaymentModule->setOrderId($oRow->id);
                    $bRes = $oPaymentModule->bindedStatusMethod($statusName);
                    if ($bRes) { //Есть разрешение
                        $bRes = $oPaymentModule->executeStatusMethod($statusName);
                        if (!$bRes) { //Не удалось совершить операцию
                            $iAfterStatus = Status::getIdByFail();
                        }
                    }
                }
            }

            $oChangeRowStatus = new ChangeStatus();
            $oChangeRowStatus->change_date = date('Y-m-d H:i:s');
            $oChangeRowStatus->id_old_status = $iBeforeStatus;
            $oChangeRowStatus->id_new_status = $iAfterStatus;
            $oChangeRowStatus->id_order = $iOrderId;
            $oChangeRowStatus->save();

            $aVars = $oRow->getData();

            $aOrderFields4Mail = AdmOrder\ar\Order::getModel()->getColumnSet('mail');

            $aDataOrder = $oRow->getDataOrder($aOrderFields4Mail);

            $aGoods = ar\Goods::find()
                ->where('id_order', $iOrderId)
                ->asArray()->getAll();

            $totalPrice = 0;
            $isArticle = Cart\Api::isArticle();

            $aGoodsListId = [];
            foreach ($aGoods as $item) {
                $aGoodsListId[] = $item['id_goods'];
                $totalPrice += $item['total'];
            }

            $webrootpath = Site::httpDomain();
            if ($aGoodsListId) {
                $aGoodsList = catalog\GoodsSelector::getList(catalog\Card::DEF_BASE_CARD)
                    ->condition('id IN ?', $aGoodsListId)
                    ->parse();

                $aGoodsList = ArrayHelper::index($aGoodsList, 'id');

                foreach ($aGoods as &$item) {
                    $item['object'] = (isset($aGoodsList[$item['id_goods']])) ? $aGoodsList[$item['id_goods']] : false;
                    $item['webrootpath'] = $webrootpath;

                    if ($isArticle && !empty($item['object']) && !empty(ArrayHelper::getValue($item['object'], 'fields.article.value'))) {
                        $item['article'] = ArrayHelper::getValue($item['object'], 'fields.article.value');
                    }
                }
            }

            $aMailParams = [
                'totalPrice' => $totalPrice,
                'orderId' => $iOrderId,
                'items' => $aDataOrder,
                'date' => $aVars['date'],
                'aGoods' => $aGoods,
                'isArticle' => $isArticle,
            ];

            if ($oRow->price_delivery) {
                $aMailParams['deliveryPrice'] = (int) $oRow->price_delivery;
                $aMailParams['totalPriceToPay'] = $totalPrice + $aMailParams['deliveryPrice'];
            }

            $out = Parser::parseTwig('mail.twig', $aMailParams, __DIR__ . '/templates/');

            if ($oStatus->send_user) {
                $sTitle = ModulesParams::getByName('order', 'title_change_status_mail');
                $sBody = ModulesParams::getByName('order', 'status_content');

                Service::sendMail($oRow->mail, $sTitle, $sBody, [
                    'order_id' => $iOrderId,
                    'before_status' => $aStatus[$iBeforeStatus],
                    'order_info' => $out,
                    'token' => $oRow->token,
                    'after_status' => $aStatus[$iAfterStatus],
                ]);
            }

            if ($oStatus->send_admin) {
                $formAggregate = (new forms\service\FormService())
                    ->getFormByName(Cart\OrderEntity::tableName());

                $sBody = ModulesParams::getByName(
                    'order',
                    'status_paid_content'
                );
                $sTitle = ModulesParams::getByName(
                    'order',
                    'title_status_paid'
                );

                Service::sendMail(Site::getAdminEmail(), $sTitle, $sBody, [
                    'order_id' => $iOrderId,
                    'before_status' => $aStatus[$iBeforeStatus],
                    'order_info' => $formAggregate->settings->noSendDataInLetter ? '' : $out,
                    'token' => $oRow->token,
                    'after_status' => $aStatus[$iAfterStatus],
                ]);
            }
        }
    }

    /**
     * * Меняем статус заказа (для робокассы).
     *
     * @param $iOrderId
     * @param $iStatus
     * @param $iTotal
     *
     * @throws \yii\db\Exception
     *
     * @return bool|int
     */
    public static function changeStatus($iOrderId, $iStatus, $iTotal)
    {
        $iTotalPrice = (new \yii\db\Query())
            ->from(AdmOrder\ar\Goods::getTableName())
            ->where(['id_order' => $iOrderId])
            ->sum('total');

        if ($iTotalPrice) {
            return \Yii::$app->db->createCommand()
                ->update(
                    'orders',
                    ['status' => ($iTotalPrice <= $iTotal) ? $iStatus : Status::getIdByFail()],
                    ['id' => $iOrderId]
                )->execute();
        }

        return false;
    }

    /**
     * Проверка на наличие такого заказа и что у него еще не присвоен статус
     *
     * @param $iOrderId
     * @param $iSetStatus
     *
     * @return bool
     */
    public static function checkStatus($iOrderId, $iSetStatus)
    {
        /** @var AdmOrder\ar\OrderRow $oOrder */
        $oOrder = AdmOrder\ar\Order::find($iOrderId);

        if ($oOrder === null) {
            return false;
        }

        if ($oOrder->status == $iSetStatus) {
            return false;
        }

        return true;
    }

    /**
     * Удаление записей корзин старше 30 дней.
     *
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     *
     * @return bool
     */
    public static function removeExpiredCarts()
    {
        $oCurrentData = new \DateTime('now');

        /** @var models\Cart $item */
        foreach (models\Cart::find()->each() as $item) {
            $oDiff = $oCurrentData->diff(new \DateTime($item->last_modified_date));

            // Запись старше 30 дней
            if ($oDiff->days >= 30) {
                $item->delete();
            }
        }

        return true;
    }
}
