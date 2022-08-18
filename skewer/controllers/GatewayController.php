<?php

namespace skewer\controllers;

use skewer\base\Twig;
use skewer\components\gateway;

class GatewayController extends Prototype
{
    /**
     * Экземпляр сервера.
     *
     * @var null|gateway\Server
     */
    protected $oGatewayServer;

    /**
     *  Вызывается после прихода заголовка.
     *
     * @param gateway\Server $oServer
     */
    public function onHeaderLoad(&$oServer)
    {
        /* Здесь смотрим - если есть флаг того, что пакет зашифрован, то пытаемся по заголовкам
           определить, кто прислал пакет и получить ключ площадки */

        $oServer->setKey(APPKEY);
    }

    // func

    /**
     * Шлюз работает всегда.
     */
    public function isAllowedStart()
    {
        return true;
    }

    // func

    public function actionIndex()
    {
        set_time_limit(0);

        /* Режим отладки дляшаблонизатора */
        Twig::enableDebug();

        $this->oGatewayServer = new gateway\Server(gateway\Server::StreamTypeEncrypt);

        $this->registerAliasList();

        $this->oGatewayServer->addParentClass('skewer\base\site\ServicePrototype');

        $oCrypt = new gateway\blowfish\Encryptor();
        $oCrypt->setIv(\Yii::$app->getParam(['security', 'vector']));

        $this->oGatewayServer->onLoadHeaderHandler([$this, 'onHeaderLoad']);
        $this->oGatewayServer->onEncrypt([$oCrypt, 'encrypt']);
        $this->oGatewayServer->onDecrypt([$oCrypt, 'decrypt']);

        $this->oGatewayServer->handler();
    }

    private function registerAliasList()
    {
        class_alias('skewer\base\site\HostTools', 'HostTools');
        class_alias('skewer\build\Tool\Messages\Service', 'MessagesToolService');
    }
}
