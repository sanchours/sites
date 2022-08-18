<?php

namespace skewer\components\design;

use skewer\libs\Compress\ChangeAssets;
use yii\base\Component;
use yii\web\AssetConverterInterface;

/**
 * Специализированный компонент для компиляции css файлов на основе
 * настроек дизайнерского режима.
 */
class CssConverter extends Component implements AssetConverterInterface
{
    /**
     * @var array the commands that are used to perform the asset conversion.
     * The keys are the asset file extension names, and the values are the corresponding
     * target script types (either "css" or "js") and the commands used for the conversion.
     *
     * You may also use a path alias to specify the location of the command:
     *
     * ```php
     * [
     *     'styl' => ['css', '@app/node_modules/bin/stylus < {from} > {to}'],
     * ]
     * ```
     */
    public $commands = [];
    /**
     * @var bool whether the source asset file should be converted even if its result already exists.
     * You may want to set this to be `true` during the development stage to make sure the converted
     * assets are always up-to-date. Do not set this to true on production servers as it will
     * significantly degrade the performance.
     */
    public $forceConvert = false;

    /**
     * Converts a given asset file into a CSS or JS file.
     *
     * @param string $asset the asset file path, relative to $basePath
     * @param string $basePath the directory the $asset is relative to
     *
     * @return string the converted asset file path, relative to $basePath
     */
    public function convert($asset, $basePath)
    {
        $pos = mb_strrpos($asset, '.');

        if ($pos !== false) {
            $ext = mb_substr($asset, $pos + 1);

            if ($ext == 'css') {
                $fileName = mb_substr($asset, 0, mb_strlen($asset) - 4);
                $newFileName = $fileName . '.compile.css';
                $fullFileName = $basePath . '/' . $newFileName;

                // перестраиваем если файла нет или нужно обновить css
                if (!is_file($fullFileName) or \Yii::$app->assetManager->forceCopy) {
                    $oCSSParser = new CssParser();
                    $oDesignManager = new DesignManager();

                    $oCSSParser->aParams = $oDesignManager->getParams(true);

                    $content = $oCSSParser->parseFile("{$basePath}/{$asset}");

                    $h = fopen($fullFileName, 'w');
                    if (mb_strpos($content, ChangeAssets::STRING_IGNORE_CSS_FILE) === false) {
                        if (!preg_match("/\.compile\./", $asset)) {
                            $content = ChangeAssets::cssMin($content);
                        }
                    }

                    // удаление комментариев
                    $content = preg_replace('/\/\*.*\*\//sU', '', $content);

                    // Если контент пуст, то запишем в файл пустоту. При подключении файлов к странице файлы размером 0Кб не учитываются
                    if (mb_strlen($content) == 0) {
                        $content = '';
                    }

                    fwrite($h, $content);
                    fclose($h);
                }

                return $newFileName;
            }
        }

        return $asset;
    }
}
