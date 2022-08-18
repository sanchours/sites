<?php

namespace app\skewer\console;

use yii\console\Controller;
use yii\helpers\Console;

/**
 * Прототип для консольных утилит
 *
 * Нужно занаследовать класс от этого класса и уложить рядом в тот же namespace
 *
 * Если назвать класс ImportController, то можно будет вывать его из консоли
 * php yii import
 *
 * Будет вызван метод actionIndex
 *
 * Можно сделать несколько методов, например actionTest и вызывать их
 * php yii import/test
 */
class Prototype extends Controller
{
    public function init()
    {
        require_once RELEASEPATH . 'base/Autoloader.php';
        \skewer\base\Autoloader::init();
    }

    /**
     * Перевод строки в выводе (или заданное количество).
     *
     * @param int $cnt количество переводов [1]
     */
    protected function br($cnt = 1)
    {
        if ($cnt > 100 or $cnt < 1) {
            $cnt = 1;
        }

        while ($cnt-- > 0) {
            $this->stdout("\r\n");
        }
    }

    /**
     * Показывает ошибку и вставляет перевод строки.
     *
     * @param string $sErrorText
     *
     * @return int
     */
    protected function showError($sErrorText)
    {
        $this->stderr($sErrorText, Console::FG_RED);
        $this->br();

        return Controller::EXIT_CODE_ERROR;
    }

    /**
     * Показывает текст и вставляет перевод строки.
     *
     * @param string $sText
     * @param int $iBrCnt количество отступов
     *
     * @return int
     */
    protected function showText($sText, $iBrCnt = 1)
    {
        $this->stdout($sText);
        $this->br($iBrCnt);

        return Controller::EXIT_CODE_NORMAL;
    }
}
