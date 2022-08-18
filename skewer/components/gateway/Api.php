<?php

namespace skewer\components\gateway;

/**
 * Класс для создания клиента для подключения к удаленному серверу.
 */
class Api
{
    /**
     * Устанавливает соединение с GatewayServer $sGatewayServer площадки. В качестве параметров аутентификации используется
     * Ключ площадки $sAppKey.
     *
     * @example
     * try {
     *
     *  $oClient = gateway\Api::createClient();
     *
     *  $oClient->addHeader('MyHeader', '123');
     *  $oClient->addMethod('TestClass', 'TestMethod', array(1,2), array(new ResTest(), 'respo'));
     *
     *  if(!$oClient->doRequest()) throw new gateway\Exception($oClient->getError());
     *
     * } catch(gateway\Exception $e) {
     *  echo $e->getMessage();
     * }
     * после корректной инициализации вернет экземпляр gateway\Client
     * @static
     *
     * @throws Exception
     *
     * @return Client
     */
    public static function createClient()
    {
        $iSiteId = defined('SITE_ID') ? SITE_ID : 0;

        if (!defined('INCLUSTER') or !INCLUSTER) {
            throw new Exception('Not in cluster (by config param)');
        }
        if (!defined('CLUSTERGATEWAY') or !CLUSTERGATEWAY) {
            throw new Exception('Gateway path not provided in config');
        }
        if (!defined('APPKEY') or !APPKEY) {
            throw new Exception('Gateway application key not provided in config');
        }
        $oClient = new Client(CLUSTERGATEWAY, $iSiteId, Client::StreamTypeEncrypt);

        $oClient->setKey(APPKEY);
        if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME']) {
            $oClient->setClientHost($_SERVER['SERVER_NAME']);
        }

        $oCrypt = new blowfish\Encryptor();
        $oCrypt->setIv(\Yii::$app->getParam(['security', 'vector']));

        $oClient->onEncrypt([$oCrypt, 'encrypt']);
        $oClient->onDecrypt([$oCrypt, 'decrypt']);

        return $oClient;
    }
}
