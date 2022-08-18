<?php

namespace skewer\build\Tool\DeliveryPayment\models;

use skewer\components\sluggable\SluggableBehavior;
use yii\base\UserException;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "orders_payment".
 *
 * @property int $id
 * @property string $title
 * @property int $alias
 * @property string $payment
 * @property int $active
 * @property string $message
 * @property int $priority
 */
class TypePayment extends \skewer\components\ActiveRecord\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders_payment';
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
            [['priority', 'active'], 'integer'],
            [['active'], 'checkLink'],
            [['title', 'alias', 'message'], 'string', 'max' => 255],
            [['payment'], 'string', 'max' => 64],
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
            'payment' => \Yii::t('deliverypayment', 'field_payment'),
            'alias' => \Yii::t('deliverypayment', 'field_alias'),
            'active' => \Yii::t('deliverypayment', 'field_active'),
            'message' => \Yii::t('deliverypayment', 'field_message'),
        ];
    }

    public static function getNewRow($aData = [])
    {
        $oRow = new TypePayment();

        $oRow->active = 1;
        if ($aData) {
            $oRow->setAttributes($aData);
        }

        return $oRow;
    }

    /**
     * @param mixed $json
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getPayments($json = false)
    {
        $aPayments = TypePayment::find()->where(['active' => 1])->orderBy('priority')->asArray()->all();
        if ($json) {
            $aPayments = ArrayHelper::index($aPayments, 'priority');
        } else {
            $aPayments = ArrayHelper::map($aPayments, 'id', 'title');
        }

        return $aPayments;
    }

    /**
     * @return ActiveQuery
     */
    public function getDelivery()
    {
        return $this->hasMany(TypeDelivery::className(), ['id' => 'delivery_id'])
            ->viaTable('orders_delivery_payment', ['payment_id' => 'id']);
    }

    /**
     * @return array|bool
     */
    public function checkLink()
    {
        $aDelivery = ArrayHelper::getColumn($this->getDelivery()->asArray()->all(), 'title');

        return $aDelivery ?: false;
    }

    /**
     * @throws UserException
     */
    public function prohibDeactivate()
    {
        if (!$this->active) {
            $aDelivery = $this->checkLink();
            if ($aDelivery) {
                throw new UserException('Тип оплаты выбран в способах доставки: ' . implode(',', $aDelivery));
            }
        }
    }
}
