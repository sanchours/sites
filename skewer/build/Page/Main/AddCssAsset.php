<?php

namespace skewer\build\Page\Main;

use skewer\build\Design\CSSEditor\models\CssFiles;
use yii\web\AssetBundle;
use yii\web\View;

/**
 * Класс подключает набор дополнительных css файлов, задынных в дихайнерском режиме.
 * Файлы компилируются в один.
 * Парсинг css параметров работает как в обычных файлах сборки.
 */
class AddCssAsset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Main/add_css_web/';

    public $css = [
        'css/add.css',
    ];

    public $cssOptions = [
        'position' => View::POS_HEAD,
    ];

    public function init()
    {
        parent::init();
        $this->publishOptions['afterCopy'] = static function ($from, $to) {
            if (is_file($from)) {
                // собрать все файлы
                $oFiles = CssFiles::find()
                    ->where(['active' => '1'])
                    ->orderBy(['priority' => SORT_ASC])
                    ->asArray()
                    ->all();

                // объединить контент всех файлов
                $sTmpText = '';
                foreach ($oFiles as $item) {
                    $sTmpText .= $item['data'] . "\n\n";
                }

                // сохранить временный файл
                $handle = fopen($to, 'w+');
                fwrite($handle, $sTmpText);
                fclose($handle);
            }

            return true;
        };
    }
}
