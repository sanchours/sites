<?php

namespace skewer\build\Page\CategoryViewer;

use skewer\build\Adm\CategoryViewer\models\CategoryViewerCssParams;
use yii\helpers\ArrayHelper;
use yii\web\AssetBundle;

class AssetPrototype extends AssetBundle
{
    /** @var array Разделы выводимые в разводку */
    public $aActiveSections = [];

    public function publish($am)
    {
        if ($this->sourcePath !== null && !isset($this->basePath, $this->baseUrl)) {
            list($this->basePath, $this->baseUrl) = $am->publish($this->sourcePath, $this->publishOptions);
        }

        $aAllCustomSections = CategoryViewerCssParams::getSectionsId();

        $aCustomSections = array_intersect($aAllCustomSections, $this->aActiveSections);

        /** @var string хэш разделов с уникальными параметрами разводки */
        $sHashSections = md5(implode(',', $aCustomSections));

        $sFileName = "categories_{$sHashSections}.css";
        $sFullFileName = "{$this->basePath}/css/{$sFileName}";

        if (!file_exists($sFullFileName) or \Yii::$app->assetManager->forceCopy) {
            $sDefaultCss = $this->parseDefaultCss();
            $sCustomCss = $this->parseCustomCss($aCustomSections);

            $sParsedContent = $sDefaultCss . $sCustomCss;

            $handle = fopen($sFullFileName, 'w');
            fwrite($handle, $sParsedContent);
            fclose($handle);
        }

        $this->css[] = "css/{$sFileName}";

        /*
         * Вызываем родительский метод для того чтобы отконвертивароть файлы $this->css и $this->js(компонент yii\web\AssetConverterInterface)
         * Повторная публикация не произойдет!
         */
        parent::publish($am);
    }

    public static function register($view, $aParams = [])
    {
        $oAsset = new static();
        $oAsset = \Yii::configure($oAsset, $aParams);
        \Yii::$app->assetManager->bundles[self::className()] = (array) $oAsset;
        parent::register($view);
    }

    /**
     * Парсит шаблон c общими настройками разводки.
     *
     * @throws \Exception
     *
     * @return string - распарсенные данные
     */
    private function parseDefaultCss()
    {
        $sFileNameDefaultCss = dirname(\Yii::getAlias($this->sourcePath)) . '/tpl_common.css';
        $sFileNameParameters = dirname(\Yii::getAlias($this->sourcePath)) . '/parameters.php';

        if (!file_exists($sFileNameDefaultCss)) {
            throw new \Exception(sprintf('Файл с общими настройками разводки [%s] не существует', str_replace(ROOTPATH, '...', $sFileNameDefaultCss)));
        }
        if (!file_exists($sFileNameParameters)) {
            throw new \Exception(sprintf('Файл с параметрами разводки [%s] не существует', str_replace(ROOTPATH, '...', $sFileNameParameters)));
        }
        $aParams = require $sFileNameParameters;

        if (!is_array($aParams) or empty($aParams)) {
            return '';
        }

        $aCommonParams = ArrayHelper::map(
            $aParams,
            static function ($item) {
                return '[' . $item['groupName'] . '.' . $item['paramName'] . ']';
            },
            static function ($item) {
                return $item['defValue'];
            }
        );

        $sContent = file_get_contents($sFileNameDefaultCss);
        $sParsedContent = str_replace(array_keys($aCommonParams), array_values($aCommonParams), $sContent);

        return $sParsedContent;
    }

    /**
     * Парсит шаблон c уникальными настройками разводки.
     *
     * @param $aSections - массив id разделов, выводимых в разводке и имеющих уникальные css параметры
     *
     * @throws \Exception
     *
     * @return string - распарсенные данные
     */
    private function parseCustomCss($aSections)
    {
        $sParsedContent = '';

        $aCssParams = CategoryViewerCssParams::getParamsBySections($aSections);

        $sFileNameCustomCss = dirname(\Yii::getAlias($this->sourcePath)) . '/tpl_custom.css';

        if (!file_exists($sFileNameCustomCss)) {
            throw new \Exception(sprintf('Файл с уникальными настройками разводки [%s] не существует', str_replace(ROOTPATH, '...', $sFileNameCustomCss)));
        }
        $aCssParamsGroupBySection = ArrayHelper::map(
            $aCssParams,
            static function ($item) {
                return '[' . $item['group'] . '.' . $item['paramName'] . ']';
            },
            static function ($item) {
                return $item['value'];
            },
            'sectionId'
        );

        $sContent = file_get_contents($sFileNameCustomCss);

        foreach ($aSections as $iSectionId) {
            $aParams = $aCssParamsGroupBySection[$iSectionId] ?? [];
            $aParams = array_merge($aParams, ['[section]' => $iSectionId]);

            $sParsedContent .= str_replace(array_keys($aParams), array_values($aParams), $sContent);
        }

        return $sParsedContent;
    }
}
