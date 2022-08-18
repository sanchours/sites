<?php

namespace skewer\build\Tool\Backup;

use skewer\base\log\Logger;
use skewer\base\site\ServicePrototype;
use skewer\components\gateway;

/**
 * @author kolesnikov, $Author$
 *
 * @version $Revision$
 * @date $Date$
 * @project JetBrains PhpStorm
 */
class Service extends ServicePrototype
{
    public static function makeBackup($sMode = 'schedule', $sComment = '')
    {
        $oClient = gateway\Api::createClient();

        $iResultStatus = 3;

        $aParam = [$sMode, $sComment];

        /* @noinspection PhpUnusedParameterInspection */
        $oClient->addMethod('HostTools', 'makeSiteBackup', $aParam, static function ($mResult, $mError) use (&$iResultStatus) {
            if ($mError) {
                $iResultStatus = 4;

                throw new \Exception($mError);
            }
        });

        if (!$oClient->doRequest()) {
            return false;
        }

        return $iResultStatus;
    }

    public static function getBackupList()
    {
        $oClient = gateway\Api::createClient();

        $aBackupList = [];

        $oClient->addMethod('HostTools', 'getBackupList', null, static function ($mResult, $mError) use (&$aBackupList) {
            if ($mError) {
                throw new \Exception($mError);
            }
            $aBackupList = $mResult;
        });

        if (!$oClient->doRequest()) {
            return false;
        }

        return $aBackupList;
    }

    /**
     * получение настроек копирование от сервиса sms.
     *
     * @throws gateway\Exception
     * @throws \Exception
     *
     * @return array|bool
     */
    public static function getBackupSetting()
    {
        $oClient = gateway\Api::createClient();

        $aSetting = [];

        $oClient->addMethod('HostTools', 'getLocalBackupSetting', null, static function ($mResult, $mError) use (&$aSetting) {
            if ($mError) {
                throw new \Exception($mError);
            }
            $aSetting = $mResult;
        });

        if (!$oClient->doRequest()) {
            return false;
        }

        return $aSetting;
    }

    /**
     * получение общекластерных настроек резервного копирования от сервиса sms.
     *
     * @static
     *
     * @throws gateway\Exception
     * @throws \Exception
     *
     * @return array|bool
     */
    public static function getBackupGlobalSetting()
    {
        $oClient = gateway\Api::createClient();

        $aSetting = [];

        $oClient->addMethod('HostTools', 'getGlobalBackupSetting', null, static function ($mResult, $mError) use (&$aSetting) {
            if ($mError) {
                throw new \Exception($mError);
            }
            $aSetting = $mResult;
        });

        if (!$oClient->doRequest()) {
            return false;
        }

        return $aSetting;
    }

    /**
     * @static
     *
     * @param $aData
     *
     * @throws gateway\Exception
     * @throws \Exception
     *
     * @return bool|int
     */
    public static function getDownloadFileToken($aData)
    {
        // id site_id mode backup_file date status comments

        $oClient = gateway\Api::createClient();

        $aParam = [$aData['id']];

        $iToken = 0;

        $oClient->addMethod('HostTools', 'getDownloadFileToken', $aParam, static function ($mResult, $mError) use (&$iToken) {
            if ($mError) {
                throw new \Exception($mError);
            }
            $iToken = $mResult;
        });

        if (!$oClient->doRequest()) {
            return false;
        }

        return $iToken;
    }

    /**
     * Восстановление площадки из бекапа $iBackupId.
     *
     * @param $iBackupId
     *
     * @throws gateway\Exception
     *
     * @return bool
     */
    public static function recoverSiteFromBackup($iBackupId)
    {
        $oClient = gateway\Api::createClient();

        /* @noinspection PhpUnusedParameterInspection */
        $oClient->addMethod('HostTools', 'recoverBackup', [$iBackupId], static function ($mResult, $mError) use ($iBackupId) {
            if ($mError) {
                Logger::dump('Error: dont recover site from backup id=' . $iBackupId);
            }
        });

        Logger::dump('Site is recover from backup id=' . $iBackupId);

        return 3;
    }
}
