<?php

namespace skewer\components\i18n\command\delete_branch;

use skewer\base\orm\Query;

/**
 * Статусы заказа.
 */
class OrderStatus extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        Query::DeleteFrom('orders_status_lang')
            ->where('language', $this->getLanguageName())
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
    }
}
