<?php

namespace skewer\build\Page\Subscribe;

use skewer\base\section\Tree;
use skewer\build\Page\Subscribe\ar\SubscribeUser;
use skewer\build\Page\Subscribe\ar\SubscribeUserRow;

class Api
{
    /**
     * @static Метод для проверки наличия адреса e-mail в базе
     *
     * @param $sEmail
     *
     * @return int
     */
    public static function checkEmail($sEmail)
    {
        /** @var SubscribeUserRow $res */
        $res = SubscribeUser::find()->where('email', $sEmail)->getOne();
        if (!$res) {
            return 0;
        }

        return $res->id;
    }

    /**
     * @static Метод удаления подписчика
     *
     * @param $sEmail
     *
     * @return int
     */
    public static function delSubscriber($sEmail)
    {
        return SubscribeUser::delete()->where('email', $sEmail)->get();
    }

    /**
     * @param $sText
     * @param SubscribeUserRow|SubscribeEntity $subscribeEntity
     *
     * @return mixed
     */
    public static function tagsReplacement($sText, $subscribeEntity)
    {
        if ($subscribeEntity) {
            $aParams = [
                'cmd' => 'confirm',
                'confirm' => $subscribeEntity->confirm,
            ];

            /*Собирем ссылку которая подтвердит подписку*/
            $sLink = 'http://' . mb_substr(WEBROOTPATH, 0, -1) . Tree::getSectionAliasPath(\Yii::$app->sections->getValue('subscribe')) . '?' . http_build_query($aParams);

            $sText = str_replace('[link_confirm]', $sLink, $sText);

            $aParams = [
                'cmd' => 'unsubscribe',
                'email' => $subscribeEntity->email,
                'token' => md5('unsub' . $subscribeEntity->email . '010'),
            ];

            /*Собирем ссылку которая отменит подписку*/
            $sLink = 'http://' . mb_substr(WEBROOTPATH, 0, -1) . Tree::getSectionAliasPath(\Yii::$app->sections->getValue('subscribe')) . '?' . http_build_query($aParams);

            $sText = str_replace('[link_unconfirm]', $sLink, $sText);
        }

        return $sText;
    }
}//class
