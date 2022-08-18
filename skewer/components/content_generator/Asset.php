<?php

namespace skewer\components\content_generator;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/components/content_generator/web';
    public $js = [
    ];

    public $css = [
        'css/blocks.css',
        'css/tablet__block.css',
        'css/mobile__block.css',
    ];

    public $depends = [
        '\skewer\libs\fontawesome_svg_with_js\Asset',
    ];

    public static $sAssetPath = null;

    public static $aBlocksIncluded = [];

    private static $iSectionId = null;

    public static function setSectionId($iSectionId)
    {
        self::$iSectionId = $iSectionId;
    }

    public function init()
    {
        parent::init();
        $this->js = array_merge($this->js, $this->prepareJsList());
        $this->css = array_merge($this->css, $this->prepareCssList());
    }

    public function createAssetPath()
    {
        $path = \Yii::getAlias($this->sourcePath);

        self::$sAssetPath = \Yii::$app->assetManager->publish($path);
    }

    /**
     * Собирает список блоков которые присутствуют на странице.
     *
     * @param mixed $sContent
     */
    public static function createBlockList($sContent)
    {
        $matches = [];
        $pattern = '/<!--([a-z0-9\_]+)-->/';
        preg_match_all($pattern, $sContent, $matches);

        if (isset($matches[1])) {
            self::$aBlocksIncluded = array_merge(self::$aBlocksIncluded, $matches[1]);
        }
    }

    /**
     * Готовит список JS которые нужны для контентных блоков на этой странице.
     *
     * @return array
     */
    private function prepareJsList()
    {
        if (self::$iSectionId !== null) {
            $aOutJs = [];

            $aAllBlocks = Config::getItems();
            foreach (self::$aBlocksIncluded as $sBlockName) {
                if (isset($aAllBlocks['templates'][$sBlockName]) && (isset($aAllBlocks['templates'][$sBlockName]['js']))) {
                    $aOutJs = array_merge($aOutJs, $aAllBlocks['templates'][$sBlockName]['js']);
                }
            }
        } else {
            $aOutJs = $this->js;
        }

        return $aOutJs;
    }

    /**
     * Готовит список CSS которые нужны для контентных блоков на этой странице.
     *
     * @return array
     */
    private function prepareCssList()
    {
        if (self::$iSectionId !== null) {
            $aOutCss = [];
            $aAllBlocks = Config::getItems();

            foreach (self::$aBlocksIncluded as $sBlockName) {
                if (isset($aAllBlocks['templates'][$sBlockName]) && (isset($aAllBlocks['templates'][$sBlockName]['css']))) {
                    $aOutCss = array_merge($aOutCss, $aAllBlocks['templates'][$sBlockName]['css']);
                }
            }
        } else {
            $aOutCss = $this->css;
        }

        return $aOutCss;
    }

    public static function getAssetImg($sImg)
    {
        return self::$sAssetPath[1] . $sImg;
    }

    public function getHash($path)
    {
        return 'content_generator';
    }
}
