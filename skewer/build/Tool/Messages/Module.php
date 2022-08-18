<?php

namespace skewer\build\Tool\Messages;

use skewer\base\site_module\Parser;
use skewer\build\Tool;
use skewer\components\ext;

class Module extends Tool\LeftList\ModulePrototype
{
    /**
     * Перед запуском
     *
     * @return bool|void
     */
    protected function preExecute()
    {
    }

    /**
     * Первичное состояние.
     */
    protected function actionInit()
    {
        $this->actionList();
    }

    /**
     * Список сообщений.
     */
    protected function actionList()
    {
        $this->render(new Tool\Messages\view\Index([
            'aMessages' => Api::getMessages(),
        ]));
    }

    /**
     * Показывает сообщение.
     */
    protected function actionMsgShow()
    {
        $oForm = new ext\UserFileView('Message');
        $this->addLibClass('MessageView');

        $oForm->addDockedItem([
            'text' => \Yii::t('messages', 'back'),
            'action' => 'list',
            'iconCls' => 'icon-cancel',
        ]);

        $this->setCmd('load');

        $data = $this->get('data');
        $msgId = (isset($data['id'])) ? $data['id'] : false;
        $message = Api::getMessageById($msgId);
        $body = Parser::parseTwig('message.twig', $message, BUILDPATH . 'Tool/Messages/templates/');

        if ($message['new']) {
            Api::setMessageRead($msgId);
        }

        $this->setData('message', $body);

        $this->fireJSEvent('reloadMessageBar');

        $this->setInterface($oForm);
    }

    /**
     * Удаляет сообщение.
     */
    protected function actionMsgDelete()
    {
        $data = $this->get('data');
        $msgId = (isset($data['id'])) ? $data['id'] : false;

        Api::delMessage($msgId);
        $this->actionList();
    }
}
