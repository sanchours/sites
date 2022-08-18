<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 27.10.2016
 * Time: 15:52.
 */

namespace skewer\components\tokensAuth;

class Gateway
{
    /**
     * Отправка данных на удаленный UTL.
     *
     * @param $sUrl
     * @param $aData
     *
     * @return mixed
     */
    public static function getData($sUrl, $aData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sUrl); // set url to post to
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        //  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// allow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POST, 1); // set POST method
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($aData)); // add POST fields
        $sAnswer = curl_exec($ch); // run the whole process
        curl_close($ch);

        return $sAnswer;
    }
}
