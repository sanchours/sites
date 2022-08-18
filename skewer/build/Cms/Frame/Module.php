<?php

namespace skewer\build\Cms\Frame;

use skewer\base\site_module;
use skewer\build\Tool\UnderConstruction\Api as ApiUnderConst;
use skewer\components\design\Design;

/**
 * Class Module.
 */
class Module extends site_module\Prototype
{
    public function init()
    {
        $this->setParser(parserPHP);
        if (ApiUnderConst::isShowBlock()) {
            $aSetData = Api::showBlock($sTemplate);
            foreach ($aSetData as $sLabel => $aData) {
                $this->setData($sLabel, $aData);
            }
            $this->setTemplate($sTemplate);
        } else {
            $bOldAdmin = mb_strpos(\Yii::$app->request->absoluteUrl, 'oldadmin') !== false;
            $this->setTemplate($bOldAdmin ? 'index.php' : 'index_new.php');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function autoInitAsset()
    {
        return false;
    }

    public function execute()
    {
        if (!Api::isValidBrowser()) {
            $this->setTemplate('not_valid.php');
        }

        $oProcessSession = new site_module\ProcessSession();
        $sTicket = $oProcessSession->createSession();

        $this->setData('sessionId', $sTicket);
        $this->setData('layoutMode', $this->getStr('mode', 'Cms'));
        $this->setData('moduleDir', $this->getModuleWebDir());
        $this->setData('dictVals', json_encode($this->getDictVals()));
        $this->setData('ver', Design::getLastUpdatedTime());
        $this->setData('lang', \Yii::$app->i18n->getTranslateLanguage());

        return psComplete;
    }

    // func

    /**
     * Отдает набор языковых метод для работы интерфейса.
     *
     * @return array()
     */
    private function getDictVals()
    {
        return $this->parseLangVars(self::getLangKeys());
    }

    /**
     * Отдает набор ключей языковых меток для первичного отстроения админского интерфейса
     * @return array
     */
    public static function getLangKeys()
    {
        return [
            'fileBrowserSelect',
            'fileBrowserFile',
            'galleryBrowserSelect',
            'galleryBrowserNew',
            'galleryBrowserNewConfirm',
            'coordinatesButtonText',
            'mapButtonText',
            'mapButtonClean',
            'delRowHeader',
            'delRowsHeader',
            'delRow',
            'delRowNoName',
            'delRowsNoName',
            'allowDoHeader',
            'confirmHeader',
            'clear',
            'start',
            'end',
            'editorCloseConfirmHeader',
            'editorCloseConfirm',
            'error',
            'ajax_error',
            'upload',
            'yes',
            'no',
            'sectionEditing',
            'loading',
            'edit',
            'delete',
            'installAll',
            'resetAll',
            'responseFromServer',
            'fileUploadedSuccessfully',
            'errorLoadingFile',
            'totalPaginationCount'
        ];
    }

    // func
}// class
