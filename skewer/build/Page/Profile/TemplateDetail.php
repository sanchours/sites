<?php

declare(strict_types=1);

namespace skewer\build\Page\Profile;

use skewer\build\Catalog\Goods\Exception;
use skewer\components\forms\entities\FormEntity;
use skewer\components\forms\entities\FieldEntity;
use yii\helpers\ArrayHelper;
use skewer\build\Page\Cart\OrderOneClickEntity;
use skewer\build\Page\Cart\OrderEntity;

class TemplateDetail
{
    public $mainParams = [
        'person' => 'name',
        'postcode' => 'postcode',
        'address' => 'address',
        'phone' => 'phone',
        'mail' => 'email',
        'type_payment_text' => 'tp_pay',
        'type_delivery_text' => 'tp_deliv',
    ];

    private $fieldsOneClick;
    private $fieldsOrder;

    /**
     * TemplateDetail constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $oneClick = OrderOneClickEntity::tableName();
        $oneClickForm = FormEntity::getBySlug($oneClick);

        $order = OrderEntity::tableName();
        $orderForm = FormEntity::getBySlug($order);

        if (
            $oneClickForm instanceof FormEntity
            && $orderForm instanceof FormEntity
        ) {
            $fieldsOneClick = ArrayHelper::getColumn(
                FieldEntity::getFieldsByIdForm($oneClickForm->id),
                'slug'
            );
            $fieldsOrderForm = ArrayHelper::getColumn(
                FieldEntity::getFieldsByIdForm($orderForm->id),
                'slug'
            );

            $this->fieldsOneClick = $fieldsOneClick;
            $this->fieldsOrder = $fieldsOrderForm;

            return true;
        }

        throw new Exception("Не удалось найти формы: {$oneClick} и {$order}");
    }

    /**
     * @param $order
     * @return mixed
     * @throws Exception
     */
    public function getDetailOrderFields($order)
    {
        foreach ($this->mainParams as $keyOrder => $keyForm) {
            if (
                in_array($keyForm, $this->fieldsOneClick)
                or in_array($keyForm, $this->fieldsOrder)
            ) {
                continue;
            }

            if (!isset($order[$keyOrder])) {
                throw new Exception(
                    "Не удалось найти поле \"{$keyOrder}\" в таблице заказов"
                );
            }
            unset($this->mainParams[$keyOrder]);
        }

        return array_keys($this->mainParams);
    }
}
