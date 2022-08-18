<?php

namespace skewer\controllers;

use skewer\components\content_generator\Api;
use skewer\components\content_generator\Asset;
use yii\helpers\Url;

class ContentgeneratorController extends Prototype
{
    public function actionIndex()
    {
        \Yii::$app->language = \Yii::$app->request->get('lang', 'ru');

        (new \skewer\components\content_generator\Asset())->createAssetPath();

        $aOut = Api::getAll();
        $aOut['css_paths'] = self::getAssetsCss();

        return \Yii::$app->view->renderFile(Api::getDir() . 'main.php', $aOut);
    }

    public function actionName()
    {
        \Yii::$app->language = \Yii::$app->request->get('lang', 'ru');

        (new \skewer\components\content_generator\Asset())->createAssetPath();

        $sName = \Yii::$app->request->get('name', 'none');

        Api::getOne($sName);

        $sContent = \Yii::$app->view->renderFile(Api::getDir());

        $sContent = $this->addImgSrc($sContent);

        /*Добавление метки с названием блока сначала и перевода строки в конце*/
        return '<p class="g-hidelabel"><!--' . $sName . '--></p>' . $sContent . "\r\n";
    }

    private static function getAssetsCss()
    {
        $oAsset = new Asset();

        $path = \Yii::getAlias($oAsset->sourcePath);
        $publishedPath = \Yii::$app->assetManager->publish($path);
        $sPathUrl = Url::to($publishedPath[1], true);

        $aCssPaths = [];

        foreach ($oAsset->css as $cssItem) {
            $aCssPaths[] = $sPathUrl . '/' . str_replace('.min.css', '.min.compile.css', $cssItem);
        }

        return $aCssPaths;
    }

    /**
     * Ищет в тектсе картнки и добавляет им style="width/height".
     *
     * @param $sContent
     *
     * @return mixed
     */
    private function addImgSrc($sContent)
    {
        $matches = [];
        preg_match_all(
            '/<img([\w\W]+?)\/>/',
            $sContent,
            $matches,
            PREG_PATTERN_ORDER
        );

        if (isset($matches[0])) {
            foreach ($matches[0] as &$item) {
                $src = [];
                preg_match(
                    '/"([\w\W]+?)"/',
                    $item,
                    $src
                );
                if (!empty($src[1])) {
                    $src[1] = trim($src[1]);

                    list($width, $height) = getimagesize(ROOTPATH . 'web' . $src[1]);
                    $itemNew = str_replace(' src', ' style="width:' . $width . 'px;height:' . $height . 'px; margin: 0px;" src', $item);
                    $sContent = str_replace($item, $itemNew, $sContent);
                }
            }
        }

        return $sContent;
    }
}
