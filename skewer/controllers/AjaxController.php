<?php

namespace skewer\controllers;

use skewer\base\site\Layer;
use skewer\base\site_module;
use skewer\build\Cms\FileBrowser;
use skewer\components\auth\CurrentAdmin;
use skewer\components\design\Design;
use skewer\components\forms\components\protection\Captcha;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class AjaxController extends Prototype
{
    public function actionAjax()
    {
        $sCmd = site_module\Request::getStr('cmd', false);
        $sModuleName = site_module\Request::getStr('moduleName', false);

        if (!$sCmd) {
            throw new BadRequestHttpException('No `cmd` provided');
        }
        if (!$sModuleName) {
            throw new BadRequestHttpException('No `moduleName` provided');
        }
        $sLanguage = site_module\Request::getStr('language', '', \Yii::$app->language);
        \Yii::$app->language = $sLanguage;

        $realClassPath = site_module\Module::getClassOrExcept($sModuleName, Layer::PAGE);

        if (!is_subclass_of($realClassPath, 'skewer\base\site_module\Ajax')) {
            throw new ForbiddenHttpException("Class [{$sModuleName}] do not implements Ajax");
        }
        $iCurrentSection = site_module\Request::getStr('sectionId', '', false);

        if ($iCurrentSection) {
            \Yii::$app->environment->set('sectionId', (int) $iCurrentSection);
        }

        $oProcess = \Yii::$app->processList->addProcess(new site_module\Context('out', $realClassPath, ctModule, []));
        \Yii::$app->processList->executeProcessList();

        $oProcess->render();
        $aOut = [
            'html' => \Yii::$app->router->modifyOut($oProcess->getOuterText()),
            'status' => $oProcess->getStatus(),
        ];

        if (count($aData = $oProcess->getData())) {
            $aOut['data'] = $aData;
        }

        return json_encode($aOut);
    }

    public function actionCaptcha()
    {
        //$v = isset($_GET['v']) ? $_GET['v'] : 0;
        $h = $_GET['h'] ?? 'none';
        $oCaptcha = Captcha::getInstance();
        $oCaptcha->setFont(18, '#000', ''); //BUILDPATH.'common/fonts/palab.ttf'
        $oCaptcha->setSize(90, 40);
        $oCaptcha->show($h);
        exit;
    }

    public function actionUploader()
    {
        if (!CurrentAdmin::isLoggedIn()) {
            return false;
        }

        $sSelectMode = site_module\Request::getStr('selectMode');

        switch ($sSelectMode) {
            case 'designFileBrowser':
                $mSectionId = Design::imageDirName;
                break;
            default:
                $mSectionId = site_module\Request::getStr('section');
                break;
        }

        if ($sFolderAlias = site_module\Request::getStr('folder_alias')) {
            try {
                $mSectionId = FileBrowser\Api::getSectionIdbyAlias($sFolderAlias);
            } catch (\Exception $e) {
            }
        }

        // Если не найден, то установить раздел загрузки изображений по умолчанию
        $mSectionId or $mSectionId = FileBrowser\Api::getSectionIdbyAlias(FileBrowser\Api::DEF_LIB_ALIAS);

        $aFiles = \skewer\build\Adm\Files\Api::uploadFiles($mSectionId);

        if (!$aFiles['loaded']) {
            return json_encode([
                'file' => '',
                'success' => false,
                'message' => nl2br(array_shift($aFiles['errors'])),
            ]);
        }

        $sFileName = sprintf(
            '/files/%s/%s',
            $mSectionId,
            array_shift($aFiles['files'])
        );

        return json_encode([
            'file' => $sFileName,
            'success' => true,
        ]);
    }
}
