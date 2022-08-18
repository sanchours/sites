<?php

namespace skewer\components\i18n\command\switch_language;

use skewer\base\orm\Query;
use skewer\build\Adm\Order\model\Status;

/**
 * Перепись параметров модулей.
 */
class OrderStatus extends Prototype
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        Query::DeleteFrom('orders_status_lang')->where('language', $this->getNewLanguage())->get();

        /**
         * Скопировать переводы статусов.
         */
        $aStatusList = Status::find()->multilingual()->all();

        if ($aStatusList) {
            /** @var Status $oStatus */
            foreach ($aStatusList as $oStatus) {
                $sValue = \Yii::t('data/order', 'status_' . $oStatus->name, [], $this->getNewLanguage());
                if ($sValue == 'status_' . $oStatus->name) {
                    $sValue = $oStatus->getLangAttribute('title_' . $this->getOldLanguage());
                }

                $sValue = (string) $sValue;

                Query::InsertInto('orders_status_lang')
                    ->set('status_id', $oStatus->id)
                    ->set('language', $this->getNewLanguage())
                    ->set('title', $sValue)
                    ->set('active', (int) $oStatus->getLangAttribute('active_' . $this->getOldLanguage()))
                    ->get();
            }
        }

        Query::DeleteFrom('orders_status_lang')->where('language', $this->getOldLanguage())->get();
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        Query::DeleteFrom('orders_status_lang')->where('language', $this->getNewLanguage())->get();
    }
}
