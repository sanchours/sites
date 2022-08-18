<?php

namespace skewer\build\Tool\Subscribe;

use skewer\base\log\models\Log;
use skewer\base\queue as QM;
use skewer\helpers\Mailer;

/**
 * Задача по рассылке
 * Class Task.
 */
class Task extends QM\Task
{
    /** @var int id Рассылки */
    private $subscribeId = 0;

    /** @var int Максимальное кол-во писем на одну итерацию рассылки */
    private $iMaxCount = 50;

    /** @var int Счетчик отправленных */
    private $iCount = 0;

    /** @var int id текста */
    private $iTextId = 0;

    /** @var int Последний отправленный */
    private $iLastPos = 0;

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function init()
    {
        $args = func_get_args();

        $this->subscribeId = $args[0]['subscribeId'] ?? 0;

        if (!$this->subscribeId) {
            throw new \Exception('Subscribe not found');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function recovery()
    {
        $args = func_get_args();

        $this->subscribeId = $args[0]['subscribeId'] ?? 0;

        if (!$this->subscribeId) {
            throw new \Exception('Subscribe not found');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reservation()
    {
        $this->setParams(['subscribId' => $this->subscribeId]);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeExecute()
    {
        Api::updSubscribeStatus($this->iTextId, Api::statusSending);
        $this->iCount = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->iCount >= $this->iMaxCount) {
            $this->setStatus(static::stInterapt);

            return false;
        }

        //выбираем рассылку
        $iMutexToken = Api::mutMailer($this->subscribeId);
        $aCurMailer = Api::getMutMailer($iMutexToken);

        foreach ($aCurMailer as $row) {
            $iPostingId = $row['postingid'];
            $sUserList = $row['list'];
            $sSubject = trim($row['title']);
            $sCurBody = $row['text'];
            $iLastPos = $row['last_pos'];
            $this->iTextId = $row['textid'];

            Api::updSubscribeStatus($this->iTextId, Api::statusWaiting);

            //получаем список пользователей
            $aUserList = explode(',', $sUserList);

            for ($i = $iLastPos; $i < count($aUserList); ++$i) {
                $this->iLastPos = $i;
                $sTargetMail = trim(str_replace(',', '', $aUserList[$i]));

                if (($sTargetMail) and (filter_var($sTargetMail, FILTER_VALIDATE_EMAIL))) {
                    /** Отправляем */
                    $aParams = Api::getMailLabel($sTargetMail);
                    $success = Mailer::sendReadyMail($sTargetMail, $sSubject, $sCurBody, $aParams);

                    if (!$success) {
                        /* Письмо не ушло! */
                        Api::updateLastPostMailer($iPostingId, $i + 1);
                        Log::addToLog('Mail send error!', "Can't send mail to " . $sTargetMail, 'subscribe', '', Log::logCron);
                    }

                    ++$this->iCount;
                }

                //если текущая рассылка закончена выставляем соответствующий статус
                if ($i == (count($aUserList) - 1)) {
                    $this->setStatus(static::stComplete);

                    return true;
                }
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function afterExecute()
    {
        Api::updateLastPostMailer($this->subscribeId, $this->iLastPos + 1);
    }

    /**
     * {@inheritdoc}
     */
    public function error()
    {
        Api::updSubscribeStatus($this->iTextId, Api::statusError);
    }

    /**
     * {@inheritdoc}
     */
    public function complete()
    {
        Api::setReadyMailer($this->subscribeId);

        Api::updSubscribeStatus($this->iTextId, Api::statusDone);
    }
}
