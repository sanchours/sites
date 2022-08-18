<?php

use skewer\components\config\PatchPrototype;
use skewer\components\config\UpdateException;

/**
 * @class PatchInstall
 *
 * @author user, $Author$
 *
 * @version $Revision$
 * @date $Date$
 * @project JetBrains PhpStorm
 */
class Patch82393 extends PatchPrototype
{
    public $sDescription = 'Обновление до версии 4.03';

    public $sTargetBuildName = 'canape';

    public $sCurrentBuildNumber = '0037';

    public $sTargetBuildNumber = '0403';
    /* -------------------------------- */

    public $sTargetBuildVersion = '';

    public $sTplRootPath = '';

    public $sReleasePath = '';

    public $bUpdateCache = false;

    /**
     * Базовый метод запуска обновления.
     *
     * @throws UpdateException
     * @throws \skewer\components\config\Exception
     *
     * @return bool
     */
    public function execute()
    {
        if (((int) PHP_VERSION < 7)) {
            $this->fail('CanapeCMS 4.03 не работает на PHP ниже 7 версии');
        }

        /* Перезаписываем constants */
        if (!APPKEY) {
            $this->fail('У площадки отсутствуют ключи!');
        }

        if (!USECLUSTERBUILD) {
            $this->fail('Этим патчем нельзя обновить отцепленную площадку');
        }

        if (BUILDNUMBER != $this->sCurrentBuildNumber) {
            $this->fail(sprintf(
                'Обновить можно только площадку версии [%s], а текущая [%s]',
                $this->sCurrentBuildNumber,
                BUILDNUMBER
            ));
        }

        if (YII_DEBUG || YII_ENV == 'dev') {
            $this->fail('Нельзя обновить площадку находящуюся в режиме разработки', null, true);
        }

        /* добавляем новый реестр**/
        $this->executeSQLQuery("INSERT INTO `registry_storage`(`name`, `data`) SELECT 'build_" . $this->sTargetBuildName . $this->sTargetBuildNumber . "', `data` FROM `registry_storage` WHERE `name`='build_" . $this->sTargetBuildName . $this->sCurrentBuildNumber . "'");

        /************************ Меняем версию сборки *****************************/

        $this->sTargetBuildVersion = $this->sTargetBuildName . $this->sTargetBuildNumber;

        /* Используем свою сборку либо кластерную */
        $this->sReleasePath = dirname(RELEASEPATH, 2) . DIRECTORY_SEPARATOR . $this->sTargetBuildNumber . DIRECTORY_SEPARATOR . 'skewer' . DIRECTORY_SEPARATOR;
        $this->sTplRootPath = $this->sReleasePath . 'build/common/templates/';

        $aData['appKey'] = APPKEY;
        $aData['rootPath'] = ROOTPATH;
        $aData['buildName'] = $this->sTargetBuildName;
        $aData['buildNumber'] = $this->sTargetBuildNumber;
        $aData['releasePath'] = $this->sReleasePath;
        $aData['clusterGateway'] = CLUSTERGATEWAY;
        $aData['USECLUSTERBUILD'] = USECLUSTERBUILD;

        $this->updateConstants($this->sTplRootPath, $aData);

        /*---------------*/
        $aData = [];

        $aData['buildName'] = $this->sTargetBuildName;
        $aData['inCluster'] = INCLUSTER;
        $aData['buildNumber'] = $this->sTargetBuildNumber;
        $aData['redirectItems'] = [];
        $aData['USECLUSTERBUILD'] = USECLUSTERBUILD;
        $aData['USECLUSTERBUILD'] = USECLUSTERBUILD;

        $this->updateHtaccess($this->sTplRootPath, $aData);

        return true;
    }
}
