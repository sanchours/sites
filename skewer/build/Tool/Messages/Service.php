<?php

namespace skewer\build\Tool\Messages;

use skewer\base\site\ServicePrototype;

class Service extends ServicePrototype
{
    /**
     * Принимает сообщение.
     *
     * @param $message
     * @param $sendId
     *
     * @return bool|int
     */
    public function receiveMessage($message, $sendId)
    {
        unset($message['id']);
        $message['new'] = Api::MSG_STATUS_NEW;
        $message['send_id'] = $sendId;

        return Api::updMessage($message);
    }

    /**
     * Отправляет отчет о прочтении.
     *
     * @return bool
     */
    public function sendRead()
    {
        $messages = Api::getReadMessages();

        if ($messages) {
            $messages4Send = [];
            foreach ($messages as $message) {
                Api::setMessageSendRead($message->id);
                $messages4Send[] = $message->send_id;
            }

            if ($messages4Send) {
                Api::setSendingRead($messages4Send);
            }
        }

        return true;
    }
}
