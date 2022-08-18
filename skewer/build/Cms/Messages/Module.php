<?php

namespace skewer\build\Cms\Messages;

use skewer\base\site\Site;
use skewer\base\site_module\Parser;
use skewer\build\Cms;

class Module extends Cms\Frame\ModulePrototype
{
    protected function actionInit()
    {
        $this->setCmd('init');
        $this->checkMsg();
    }

    protected function actionUpdate()
    {
        $this->setCmd('update');
        $this->checkMsg();
    }

    private function checkMsg()
    {
        if ($unreadMessages = \skewer\build\Tool\Messages\Api::getUnreadMessages()) {
            $unreadMessages['text'] = \skewer\build\Tool\Messages\Api::getMessagesSuffix($unreadMessages['count']);
        }
        $unreadMessages['link'] = Site::admUrl('Messages');
        $body = Parser::parseTwig('view.twig', $unreadMessages, BUILDPATH . 'Cms/Messages/templates/');
        $this->setCmd('init');
        $this->setData('message', $body);
    }
}
