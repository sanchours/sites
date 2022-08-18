<?php

namespace skewer\build\Page\Forms;

use skewer\base\section\Parameters;
use skewer\build\Design\Zones\Api;
use skewer\controllers\SiteController;
use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Forms/web/';

    public $css = [
        'css/forms.css',
        'css/forms_media.css',
        'css/select2_form-theme.css',
    ];

    public $js = [
        'js/jquery.ui.touch-punch.js',
        'js/formValidator.js',
        'js/jquery.validate.min.js',
        'js/jquery.custom-file-input.js',
        'js/form-init.js',
        'js/rating.js',
        'js/email.validate.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

    public $depends = [
        'skewer\libs\JqueryInputMask\Asset',
        'yii\web\JqueryAsset',
        'skewer\libs\datepicker\Asset',
        'skewer\ext\jqueryui\Asset',
        'skewer\components\rating\Asset',
        'skewer\libs\select2\Asset',
    ];

    public function init()
    {
        $this->js[] = 'js/message_' . \Yii::$app->language . '.js';
        if (SiteController::getForceCopy()) {
            $this->publishOptions['afterCopy'] = function ($from, $to) {
                $this->addFormDepends();
            };
        } else {
            $this->addFormDepends();
        }
        parent::init();
    }

    /**
     * Подключение дополнительного ассета,
     * если задан шаблон для форм
     */
    private function addFormDepends()
    {
        $sTpl = Parameters::getShowValByName(
            \Yii::$app->sections->root(),
            Api::layoutGroupName,
            'form_tpl'
        );
        $sFullPath = 'skewer\build\Page\Main\templates\form\\' . $sTpl . '\Asset';
        if (class_exists($sFullPath)) {
            \Yii::$app->view->registerAssetBundle($sFullPath);
        }
    }
}
