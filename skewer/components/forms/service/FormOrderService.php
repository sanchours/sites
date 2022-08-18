<?php

declare(strict_types=1);

namespace skewer\components\forms\service;

use skewer\components\forms\entities\FormOrderEntity;

class FormOrderService
{
    /** @var FormOrderEntity $_formOrderEntity */
    private $_formOrderEntity;

    /** @var int $ordersCount общее количество заказов */
    public $ordersCount = 0;

    public function __construct(int $idForm)
    {
        $this->_formOrderEntity = new FormOrderEntity($idForm);
    }

    public function getOrderByFilter(
        int $countLimit,
        int $shiftLimit,
        int $id = null,
        string $person = null
    ) {
        $queryFields = $this->_formOrderEntity->selectFrom()->setCounterRef(
            $this->ordersCount
        );

        if ($id !== null) {
            $queryFields->where('id', $id);
        }

        if ($person !== null) {
            $queryFields->whereRaw("person LIKE \"%{$person}%\"");
        }

        $queryFields->order('id', 'DESC')
            ->limit($countLimit, $countLimit * $shiftLimit);

        return $queryFields->getAll();
    }

    /**
     * @param array $formOrder
     *
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    public function insertFormOrder(array $formOrder)
    {
        $this->_formOrderEntity->insertFormOrder($formOrder);
    }

    /**
     * @param int $id
     * @param array $formOrder
     *
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     */
    public function updateFormOrder(int $id, array $formOrder)
    {
        $this->_formOrderEntity->updateFormOrder($id, $formOrder);
    }

    public function deleteAllFormOrders()
    {
        $this->_formOrderEntity->clearTable();
    }

    public function deleteMultipleFormOrderByIds(array $ids)
    {
        $this->_formOrderEntity->deleteMultipleByIds($ids);
    }

    public function deleteFormOrderByIds(int $id)
    {
        $this->_formOrderEntity->deleteFieldById($id);
    }
}
