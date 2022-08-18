<?php

namespace skewer\build\Cms\Frame;

use skewer\base\site\Layer;
use skewer\base\site_module\Module as SiteModule;
use skewer\build\Page\Main\Module as MainModule;
use skewer\build\Tool\UnderConstruction\Api as ApiUnderConst;
use skewer\components\auth\CurrentAdmin;
use Browser;

/**
 * Class Api.
 */
class Api
{
    public static function isValidBrowser()
    {
        $oBrowser = new Browser();
        $sBrowser = $oBrowser->getBrowser();

        $iValidVersion = \Yii::$app->getParam(['browser', $sBrowser], 0);

        if (!$iValidVersion || $oBrowser->getVersion() < $iValidVersion) {
            return false;
        }

        return true;
    }

    /**
     * Парсинг заглушки на сайт
     *
     * @param string $sTemplate
     *
     * @return array
     */
    public static function showBlock(&$sTemplate = '')
    {
        if (CurrentAdmin::isAdminPolicy()) {
            CurrentAdmin::logout();
        }

        $sTemplate = 'showBlock.php';

        $sMainTplBlock = SiteModule::getTemplateDir4Module(MainModule::getNameModule(), Layer::PAGE) . $sTemplate;

        $showBlock = \Yii::$app->getView()->renderPhpFile(
            $sMainTplBlock,
            ['showBlock' => ApiUnderConst::getDataBlock()]
        );

        return ['showBlock' => $showBlock];
    }
}
