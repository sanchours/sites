<?php

namespace skewer\components\auth;

use skewer\build\Page\Auth\AuthTicket;
use skewer\build\Page\Auth\PageUsers;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class DeleteExpiredTask extends \skewer\base\queue\Task
{
    const SCHEDULE_TASK_NAME = "delete_expired_activation";

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $expiredTickets = (new Query())
            ->from('tickets')
            ->where(['<=', 'expire', date('Y-m-d')])
            ->all();

        if (!empty($expiredTickets)) {
            $deleteUsersId = [];
            $deleteTicketsHashes = [];
            foreach ($expiredTickets as $expiredTicket) {
                $ticket = AuthTicket::get($expiredTicket['hash'], false);
                if ($ticket->getModuleName() === 'auth' && $ticket->getActionName() === 'activate') {
                    $deleteUsersId[] = $ticket->getObjectId();
                    $deleteTicketsHashes[] = $expiredTicket['hash'];
                }
            }

            PageUsers::deleteAll(['id' => $deleteUsersId]);
            \Yii::$app->db->createCommand()
                ->delete('tickets', ['hash' => ArrayHelper::getColumn($expiredTickets, 'hash')])
                ->execute();
        }

        $this->setStatus(self::stComplete);
    }
}
