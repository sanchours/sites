<?php

namespace skewer\components\ext\field;

use skewer\build\Cms\FileBrowser;
use skewer\components\auth\Auth;
use skewer\helpers\ImageResize;

/**
 * Редактор текста CKEditor.
 */
class Wyswyg extends Text
{
    const addConfigParamName = 'addConfig';

    public static $bLockTooltipModule = false;

    public function getView()
    {
        return 'wyswyg';
    }

    /** {@inheritdoc} */
    public function getDesc()
    {
        // Обновить значение value поля, для дальнейшей обработки
        $this->processParams();

        $aAddConfig = $this->getDescVal(self::addConfigParamName) ?: [];

        $aUser = Auth::getUserData('admin');

        $iVideoSection = FileBrowser\Api::getSectionIdbyAlias('Adm_Video');

        $aCongig = $aAddConfig + [
                //'bodyClass' => '',
                'contentsCss' => [
                    $this->getUrlResourse(\skewer\build\Page\Main\Asset::className(), 'css/typo.css'),
                    $this->getUrlResourse(\skewer\build\Cms\Frame\Asset::className(), 'css/wyswyg.css'),
                    $this->getUrlResourse(\skewer\libs\fontawesome_svg_with_js\Asset::className(), 'css/fa-svg-with-js.css'),
                    $this->getUrlResourse(\skewer\components\content_generator\Asset::className(), 'css/blocks.compile.css'),
                    $this->getUrlResourse(\skewer\libs\CKEditor\AssetForReactAdmin::className(), 'css/only_wys.css'),
                ],
                'addLangParams' => \skewer\build\Adm\Editor\Api::getAddLangParams4Wyswyg(),
                'sysmode' => $aUser['systemMode'],
                'lock_tooltip_module' => (int) self::$bLockTooltipModule,
                'video_section' => $iVideoSection,
            ];

        $this->setDescVal(self::addConfigParamName, $aCongig);

        // Отменить оборачивание картинок с размерами
        $this->setValue(ImageResize::restoreTags($this->getValue()));

        return parent::getDesc();
    }

    /**
     * Получить урл ресурса по имени класса комплекта ресурсов и имени ресурса.
     *
     * @param string $sBundleClassName
     * @param string $sResourceName
     *
     * @return string
     */
    private static function getUrlResourse($sBundleClassName, $sResourceName)
    {
        return \Yii::$app->getAssetManager()->getAssetUrl(
            \Yii::$app->getAssetManager()->getBundle($sBundleClassName),
            $sResourceName
        );
    }
}
