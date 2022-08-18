<?php

namespace skewer\build\Page\Main;

use skewer\base\section\Parameters;
use skewer\base\site\Layer;
use skewer\base\SysVar;
use skewer\components\design\Design;
use skewer\libs\Compress\ChangeAssets;
use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Main/web/';

    public $css = [
        'css/param.css',
        'css/layout.css',
        'css/main.css',
        //'css/superfast.css',
        'css/varcss.css',
        //'css/shcart.css',
        'css/typo.css',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

    public $depends = [
        'skewer\build\Page\Main\PrintAsset',
        'yii\web\JqueryAsset',
        'skewer\libs\datepicker\Asset',
        'skewer\libs\fancybox\Asset',
        'skewer\build\Page\Main\BaseAsset',
        'skewer\build\Page\Forms\Asset',
        'skewer\components\content_generator\Asset',
    ];

    public static $jsPath = [];
    public static $jsHash = '';
    public static $cssPath = [];
    public static $cssHash = '';

    //название файлов игнора
    const CSSHASHIGNORE = 'ignoreCSS';
    const PATHCOMPILEFILE = '/compile/';

    public function init()
    {
        if (Design::modeIsActive()) {
            array_unshift($this->css, 'css/design.css');
        }

        if (\Yii::$app->register->moduleExists('Fonts', Layer::TOOL)) {
            $this->depends[] = 'skewer\components\fonts\Asset';
        }

        parent::init();

        //формирование одного js и одного css
        \Yii::$app->view->on(\yii\base\View::EVENT_END_PAGE, function () {
            //избавление от повторной обработки
            if (!self::$cssPath || !self::$jsPath) {
                $bCompress = SysVar::get(ChangeAssets::NAMEPARAM, 1);
                if ($bCompress) {
                    $aJsIgnoreUnion = $this->getJsIgnoreUnion();
                    $aCssIgnoreUnion = $this->getCssIgnoreUnion();

                    //Получение правильных путей
                    foreach (\Yii::$app->view->jsFiles as $sAssetPath) {
                        $onlyPathJs = array_keys($sAssetPath);
                        foreach ($onlyPathJs as $jsPath) {
                            ChangeAssets::getOnlyPath($jsPath, 'js');
                        }
                    }

                    $onlyPathCss = array_keys(\Yii::$app->view->cssFiles);
                    foreach ($onlyPathCss as $cssPath) {
                        ChangeAssets::getOnlyPath($cssPath, 'css');
                    }
                    \Yii::$app->view->jsFiles = [];
                    \Yii::$app->view->cssFiles = [];
                    $directory = \Yii::$app->basePath . '/web/assets' . Asset::PATHCOMPILEFILE;
                    if (!file_exists($directory)) {
                        mkdir($directory);
                    }
                    $filenameJs = self::putData(self::$jsHash, 'js', self::$jsPath);
                    //css
                    $filenameCss = self::putData(self::$cssHash, 'css', self::$cssPath);

                    $newJsUrl = \Yii::$app->assetManager->baseUrl . Asset::PATHCOMPILEFILE . $filenameJs;
                    $newCssUrl = \Yii::$app->assetManager->baseUrl . Asset::PATHCOMPILEFILE . $filenameCss;

                    $newCssPrint = \Yii::$app->assetManager->baseUrl . '/' . SysVar::get('print_path');

                    \Yii::$app->view->registerJsFile($newJsUrl);
                    \Yii::$app->view->registerCssFile($newCssPrint, ['media' => 'print']);
                    \Yii::$app->view->registerCssFile($newCssUrl);

                    // js-файлы, не учавствующие в объединении файлов в один, подключаем отдельно
                    if ($aJsIgnoreUnion) {
                        foreach ($aJsIgnoreUnion as $aScript) {
                            \Yii::$app->view->registerJsFile($aScript['url'], $aScript['jsOptions']);
                        }
                    }

                    // css - файлы, не учавствующие в объединении файлов в один, подключаем отдельно
                    if ($aCssIgnoreUnion) {
                        foreach ($aCssIgnoreUnion as $aScript) {
                            \Yii::$app->view->registerCssFile($aScript['url']);
                        }
                    }
                }

                $this->unRegisterEmptyFiles();
            }
        });
    }

    /**
     * Получить js-файлы, не учавствующие в склейке файлов в один.
     *
     * @return array
     */
    public function getJsIgnoreUnion()
    {
        $aIgnoreFiles = [];

        foreach (\Yii::$app->view->jsFiles as $iPosition => &$aJsFiles) {
            foreach ($aJsFiles as $sAssetPath => $sHtml) {
                $isBundle = (bool) preg_match('/-*bundle\.min\.js/', $sAssetPath);
                $bDefer = (bool) preg_match('/\s+defer/', $sHtml);
                $bAsync = (bool) preg_match('/\s+async/', $sHtml);
                $bRemoteFile = (bool) preg_match('{http\:\/\/|https\:\/\/}', $sHtml);

                if ($bDefer || $bAsync || $bRemoteFile || $isBundle) {
                    $aIgnoreFiles[$sAssetPath] = [
                        'url' => $sAssetPath,
                        'jsOptions' => [
                            'defer' => $bDefer,
                            'async' => $bAsync,
                        ],
                    ];

                    unset(\Yii::$app->view->jsFiles[$iPosition][$sAssetPath]);
                }
            }
        }

        return $aIgnoreFiles;
    }

    /**
     * Получить css-файлы, не учавствующие в склейке файлов в один.
     *
     * @return array
     */
    public function getCssIgnoreUnion()
    {
        $aIgnoreFiles = [];

        foreach (\Yii::$app->view->cssFiles as $sAssetPath => $sHtml) {
            $bRemoteFile = (bool) preg_match('{http\:\/\/|https\:\/\/}', $sHtml);

            if ($bRemoteFile) {
                $aIgnoreFiles[$sAssetPath] = [
                    'url' => $sAssetPath,
                ];

                unset(\Yii::$app->view->cssFiles[$sAssetPath]);
            }
        }

        return $aIgnoreFiles;
    }

    /**
     * Удаление пустых файлов(css и js) ассетов из вывода.
     */
    protected function unRegisterEmptyFiles()
    {
        foreach (\Yii::$app->view->jsFiles as $iPosition => &$aJsFiles) {
            foreach ($aJsFiles as $sRelativePath => $sHtmlTag) {
                // Удалим времен.метку
                $sFileUrl = preg_replace('{\?v=(.)*}', '', $sRelativePath);
                $sFilePath = WEBPATH . $sFileUrl;

                if (@filesize($sFilePath) === 0) {
                    unset(\Yii::$app->view->jsFiles[$iPosition][$sRelativePath]);
                }
            }
        }

        foreach (\Yii::$app->view->cssFiles as $sRelativePath => $sHtmlTag) {
            // Удалим времен.метку
            $sFileUrl = preg_replace('{\?v=(.)*}', '', $sRelativePath);
            $sFilePath = WEBPATH . $sFileUrl;

            if (@filesize($sFilePath) === 0) {
                unset(\Yii::$app->view->cssFiles[$sRelativePath]);
            }
        }
    }

    public function putData($strForHash, $sExpansion, $aPath, $ignore = 0)
    {
        $filename = md5($strForHash) . ".{$sExpansion}";
        $newPath = \Yii::$app->basePath . '/web/assets' . Asset::PATHCOMPILEFILE . $filename;

        if (!file_exists($newPath)) {
            $sContent = '';

            foreach ($aPath as $sPath) {
                if ($sExpansion == 'css') {
                    $content = ChangeAssets::parseDataCss($sPath);
                    if (!mb_stristr($sPath, 'print.compile.css')) {
                        $sContent .= "\r\n/*" . str_replace(\Yii::$app->basePath . '/web/', '', $sPath) . "*/\r\n" . $content;
                    } else {
                        $aPatternPath = '/assets\\/([^\\)]+)/i';
                        $aPathLong = [];
                        preg_match($aPatternPath, $sPath, $aPathLong);
                        SysVar::set('print_path', $aPathLong[1]);
                    }
                } else {
                    if (!preg_match("/\.compile\.js/", $sPath)) {
                        $content = ChangeAssets::jsMin($sPath);
                    } else {
                        $content = file_get_contents($sPath);
                    }
                    $sContent .= $content;
                }
            }
            file_put_contents($newPath, $sContent);
        }

        if ($timestamp = @filemtime($newPath)) {
            $filename = "{$filename}?v={$timestamp}";
        }

        return $filename;
    }
}
