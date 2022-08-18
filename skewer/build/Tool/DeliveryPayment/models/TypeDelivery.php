<?php

namespace skewer\build\Tool\DeliveryPayment\models;

use skewer\components\sluggable\SluggableBehavior;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "orders_delivery".
 *
 * @property int $id
 * @property string $title
 * @property int $alias
 * @property int $active
 * @property int $min_cost
 * @property int $free_shipping
 * @property int $price
 * @property string $address
 * @property int $coord_deliv_costs
 * @property int $priority
 *
 * @property $payments
 */
class TypeDelivery extends \skewer\components\ActiveRecord\ActiveRecord
{
    const TABLE_DELIVERY_PAYMENT = 'orders_delivery_payment';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders_delivery';
    }

    public function behaviors()
    {
        return [
            [
                'class' => SluggableBehavior::className(),
                'attribute' => 'alias',
                'slugAttribute' => 'alias',
                'ensureUnique' => true,
                'forceUpdate' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['active', 'min_cost', 'free_shipping', 'price', 'priority', 'coord_deliv_costs'], 'integer'],
            [['title', 'alias', 'address'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => \Yii::t('deliverypayment', 'field_title'),
            'alias' => \Yii::t('deliverypayment', 'field_alias'),
            'active' => \Yii::t('deliverypayment', 'field_active'),
            'min_cost' => \Yii::t('deliverypayment', 'field_min_cost'),
            'free_shipping' => \Yii::t('deliverypayment', 'field_free_shipping'),
            'price' => \Yii::t('deliverypayment', 'field_price'),
            'address' => \Yii::t('deliverypayment', 'field_address'),
            'coord_deliv_costs' => \Yii::t('deliverypayment', 'field_coord_deliv_costs'),
        ];
    }

    public static function getNewRow($aData = [])
    {
        $oRow = new TypeDelivery();

        $oRow->active = 1;
        $oRow->min_cost = 0;
        $oRow->price = 0;
        if ($aData) {
            $oRow->setAttributes($aData);
        }

        return $oRow;
    }

    /**
     * @deprecated 34 Use saveWithLink() method instead
     *
     * @param mixed $runValidation
     * @param null|mixed $attributeNames
     */
    public function save($runValidation = true, $attributeNames = null)
    {
    }

    /**
     * @deprecated 34 Use deleteWithLink() method instead
     */
    public function delete()
    {
    }

    /**
     * @return ActiveQuery
     */
    public function getPayments()
    {
        return $this->hasMany(TypePayment::className(), ['id' => 'payment_id'])
            ->viaTable('orders_delivery_payment', ['delivery_id' => 'id']);
    }

    /**
     * Получение типа оплаты основываясь на конфигурации типа доставки
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getPaymentsForDelivery()
    {
        $activeTypeDelivery = self::find()
            ->where(['active' => true])
            ->orderBy('priority')
            ->one();

        if ($activeTypeDelivery instanceof self) {
            return TypePayment::find()
                ->rightJoin('orders_delivery_payment',
                    '`orders_delivery_payment`.`payment_id` = `orders_payment`.`id`')
                ->where(['`orders_delivery_payment`.`delivery_id`' => $activeTypeDelivery->id])
                ->asArray()
                ->all();
        }

        return [];
    }

    /**
     * @return array
     */
    public function getDeliveryPayment()
    {
        $aDeliveryPayment = $this->getPayments()
            ->orderBy('priority')
            ->asArray()
            ->all();

        return ArrayHelper::getColumn($aDeliveryPayment, 'id');
    }

    /**
     * @param mixed $json
     *
     * @return array
     */
    public function getDeliveryPaymentAsArray($json = false)
    {
        $aDeliveryPayment = $json === false
            ? $this->getPaymentsForDelivery()
            : $this->getPayments()->orderBy('priority')->asArray()->all();
        if ($aDeliveryPayment) {
            if ($json) {
                return ArrayHelper::index($aDeliveryPayment, 'priority');
            }

            return ArrayHelper::map($aDeliveryPayment, 'id', 'title');
        }

        return TypePayment::getPayments($json);
    }

    /**
     * @param $aData
     * @param bool $runValidation
     * @param null $attributeNames
     *
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\Exception
     *
     * @return bool
     */
    public function saveWithLink($aData, $runValidation = true, $attributeNames = null)
    {
        $this->setAttributes($aData);
        $transaction = static::getDb()->beginTransaction();

        try {
            $res = parent::save($runValidation, $attributeNames);

            if ($res && isset($aData['payments'])) {
                $aOldDeliveryPayment = $this->getDeliveryPayment();
                $aNewDeliveryPayment = explode(',', $aData['payments']);
                $aDelDeliveryPayment = array_diff($aOldDeliveryPayment, $aNewDeliveryPayment);
                $aInsDeliveryPayment = array_diff($aNewDeliveryPayment, $aOldDeliveryPayment);
                if ($aDelDeliveryPayment) {
                    $aObjTypePayment = TypePayment::findAll($aDelDeliveryPayment);
                    foreach ($aObjTypePayment as $objTypePayment) {
                        $this->unlink('payments', $objTypePayment, true);
                    }
                }
                if ($aInsDeliveryPayment) {
                    $aObjTypePayment = TypePayment::findAll($aInsDeliveryPayment);
                    foreach ($aObjTypePayment as $objTypePayment) {
                        $this->link('payments', $objTypePayment);
                    }
                }
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        $transaction->commit();

        return $res;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function deleteWithLink()
    {
        $transaction = static::getDb()->beginTransaction();

        try {
            parent::delete();

            $aDeliveryPayment = $this->getDeliveryPayment();
            if ($aDeliveryPayment) {
                $aObjTypePayment = TypePayment::findAll($aDeliveryPayment);
                foreach ($aObjTypePayment as $objTypePayment) {
                    $this->unlink('payments', $objTypePayment);
                }
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        $transaction->commit();
    }
}
