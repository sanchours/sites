<?php

namespace skewer\components\i18n\command\add_branch;

use skewer\base\orm\Query;
use skewer\build\Adm\Order\model\Status;

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
        Query::DeleteFrom('orders_status_lang')->where('language', $this->getLanguageName())->get();

        /**
         * Скопировать переводы статусов.
         */
        $aStatusList = Status::find()->multilingual()->all();

        if ($aStatusList) {
            /** @var Status $oStatus */
            foreach ($aStatusList as $oStatus) {
                $sValue = \Yii::t('data/order', 'status_' . $oStatus->name, [], $this->getLanguageName());
                if ($sValue == 'status_' . $oStatus->name) {
                    $sValue = $oStatus->getLangAttribute('title_' . $this->getSourceLanguageName());
                }

                Query::InsertInto('orders_status_lang')
                    ->set('status_id', $oStatus->id)
                    ->set('language', $this->getLanguageName())
                    ->set('title', (string) $sValue)
                    ->set('active', $oStatus->getLangAttribute('active_' . $this->getSourceLanguageName()))
                    ->get();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
    }
}
