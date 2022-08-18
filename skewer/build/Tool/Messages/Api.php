<?php

namespace skewer\build\Tool\Messages;

use skewer\base\log\Logger;
use skewer\base\ui\ARSaveException;
use skewer\build\Tool\Messages\models\Messages as MessagesModel;
use skewer\build\Tool\Messages\models\MessagesRead;
use skewer\components\gateway;

class Api
{
    //Типы сообщений
    const MSG_TYPE_DEFAULT = 1;     //По умолчанию
    const MSG_TYPE_EXTRA = 2;       //Экстренное
    const MSG_TYPE_IMPORTANT = 3;   //Важное
    const MSG_TYPE_WARNING = 4;     //Предупреждение

    //Новое сообщение
    const MSG_STATUS_NEW = 1;
    const MSG_STATUS_READ = 0;

    /**
     * Обновляет сообщение.
     *
     * @param $data
     *
     * @return bool|int
     */
    public static function updMessage($data)
    {
        $message = new MessagesModel();
        $message->setAttributes($data);
        $bRes = $message->save();

        if (!$bRes) {
            Logger::dumpException(new ARSaveException($message));
        }

        return $bRes;
    }

    /**
     * Возвращает сообщения.
     *
     * @return array
     */
    public static function getMessages()
    {
        $messages = MessagesModel::find()
            ->orderBy(['arrival_date' => SORT_DESC])
            ->limit(100)
            ->all();

        foreach ($messages as &$message) {
            $message->type = self::getMessageType($message->type);
            $message->arrival_date = self::getDescriptionForDate($message->arrival_date);

            if ($message->new) {
                $message->title = '<b>' . $message->title . '</b>';
                $message->type = '<b>' . $message->type . '</b>';
                $message->arrival_date = '<b>' . $message->arrival_date . '</b>';
            }
        }

        return $messages;
    }

    /**
     * Возвращает тип сообщения.
     *
     * @param $typeId
     *
     * @return string
     */
    public static function getMessageType($typeId)
    {
        switch ($typeId) {
            case self::MSG_TYPE_DEFAULT:
                return \Yii::t('messages', 'type_default');
                break;
            case self::MSG_TYPE_EXTRA:
                return \Yii::t('messages', 'type_extra');
                break;
            case self::MSG_TYPE_IMPORTANT:
                return \Yii::t('messages', 'type_important');
                break;
            case self::MSG_TYPE_WARNING:
                return \Yii::t('messages', 'type_warning');
                break;
        }

        return false;
    }

    /**
     * Удаляет сообщениеа.
     *
     * @param $msgId
     *
     * @return bool|int
     */
    public static function delMessage($msgId)
    {
        return MessagesModel::deleteAll(['id' => $msgId]);
    }

    /**
     * Возвращает сообщение по id.
     *
     * @param $msgID
     *
     * @return array
     */
    public static function getMessageById($msgID)
    {
        if ($message = MessagesModel::findOne(['id' => $msgID])->toArray()) {
            $message['type'] = self::getMessageType($message['type']);
            $message['arrival_date'] = self::getDescriptionForDate($message['arrival_date']);
        }

        return $message;
    }

    /**
     * Возвращает описание для даты.
     *
     * @param $sDate
     *
     * @return string
     */
    public static function getDescriptionForDate($sDate)
    {
        $oStartDate = new \DateTime($sDate);
        $oEndDate = new \DateTime(date('Y-m-d H:i:s', time()));
        $oDiff = $oStartDate->diff($oEndDate);

        $sStartDayNum = $oStartDate->format('m-d');
        $sTodayDayNum = $oEndDate->format('m-d');

        switch ($oDiff->days) {
            //Меньше одного дня
            case 0:

                if ($oDiff->h < 1) {
                    if ($oDiff->i == 0) {
                        $sOut = \Yii::t('messages', 'field_now');
                    } else {
                        $sOut = \Yii::t('messages', 'field_minutes', [$oDiff->i, self::getSuffix($oDiff->i)]);
                    }
                } elseif ($sStartDayNum == $sTodayDayNum) {
                    $sOut = \Yii::t('messages', 'field_today', $oStartDate->format('H:i:s'));
                } else {
                    $sOut = \Yii::t(
                        'messages',
                        'field_longago',
                        [$oStartDate->format('d.m.Y'), $oStartDate->format('H:i')]
                    );
                }
                break;

            default:
                $sOut = \Yii::t(
                    'messages',
                    'field_longago',
                    [$oStartDate->format('d.m.Y'), $oStartDate->format('H:i')]
                );
                break;
        }

        return $sOut;
    }

    /**
     * Возвращает числительное.
     *
     * @param $iNum
     *
     * @return string
     */
    public static function getSuffix($iNum)
    {
        $iLastNum = (int) mb_substr($iNum, -1);
        $iPreLastNum = (int) mb_substr(round($iNum / 10, 0), -1);

        return ($iLastNum == 1) ? (($iPreLastNum == 1) ? \Yii::t('messages', 'minutes_1') :
            \Yii::t('messages', 'minutes_2')) : (($iLastNum == 0 or $iLastNum > 4) ?
            \Yii::t('messages', 'minutes_1') : ($iPreLastNum == 1) ?
            \Yii::t('messages', 'minutes_1') : \Yii::t('messages', 'minutes_3'));
    }

    /**
     * Возвращает текст новых сообщений в зависимости от количества.
     *
     * @param $iNum
     *
     * @return string
     */
    public static function getMessagesSuffix($iNum)
    {
        $iLastNum = (int) mb_substr($iNum, -1);
        $iPreLastNum = (int) mb_substr(round($iNum / 10, 0), -1);

        return ($iLastNum == 1) ? (($iPreLastNum == 1) ? \Yii::t('messages', 'messages_1') :
            \Yii::t('messages', 'messages_2')) : (($iLastNum == 0 or $iLastNum > 4) ?
            \Yii::t('messages', 'messages_1') : ($iPreLastNum == 1) ? \Yii::t('messages', 'messages_1') :
                \Yii::t('messages', 'messages_3'));
    }

    /**
     * Читает сообщение.
     *
     * @param $msgId
     *
     * @return bool|int
     */
    public static function setMessageRead($msgId)
    {
        if (!$message = MessagesModel::findOne(['id' => $msgId])) {
            return false;
        }

        /* @var MessagesModel $message */
        $message->new = 0;
        $message->save();

        $mr = new MessagesRead();
        $mr->id = $msgId;
        $mr->send_id = $message->send_id;

        return $mr->save();
    }

    /**
     * Говорит смс, что сообщение прочитано.
     *
     * @param $sendings
     */
    public static function setSendingRead($sendings)
    {
        if (!INCLUSTER) {
            return;
        }

        $oClient = gateway\Api::createClient();
        $oClient->addMethod(
            'MessagesToolService',
            'iAmReadMessage',
            [$sendings],
            static function ($mResult, $mError) use ($sendings) {
            }
        );
        $oClient->doRequest();
    }

    /**
     * Возвращает сообщения, которые прочитаны.
     *
     * @return array
     */
    public static function getReadMessages()
    {
        return MessagesRead::find()->limit(1000)->all();
    }

    /**
     * Возвращает непрочитанные сообщения.
     *
     * @return array
     */
    public static function getUnreadMessages()
    {
        $messages = MessagesModel::find()
            ->where(['new' => 1])
            ->orderBy(['arrival_date' => SORT_DESC])
            ->limit(100)
            ->asArray()
            ->all();

        return [
            'items' => $messages,
            'count' => $messages ? count($messages) : 0,
        ];
    }

    /**
     * Отчет о доставки отправлен.
     *
     * @param $msgId
     *
     * @return bool|int
     */
    public static function setMessageSendRead($msgId)
    {
        return MessagesRead::deleteAll(['id' => $msgId]);
    }
}
