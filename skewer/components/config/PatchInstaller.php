<?php

namespace skewer\components\config;

use skewer\base\log\Logger;
use skewer\build\Tool\Patches\Api;
use yii\web\ServerErrorHttpException;

/**
 * Класс запуска обновлений на установку.
 *
 * Что делать с патчами?
 * Патчи могут запускаться 3-мя разными способами:
 * 1. Локальные патчи - лежат в директории сборки сайта и видны в админке в списке доступных либо недоступных к установке.
 * Для их запуска нужно указать путь к ним.
 * 2. Remote патчи - лежат в сборках кластера - не видны из админки запускаются в процессе установки обновлений.
 * Для запуска нужно указать путь.
 * 3.
 * Получается, что патч всегда выполняется только в рамках конкретной площадки с конкретной версией сборки. Т.е. проблем
 * с именами реестра нет.
 * В случае если на площадке с версией blue0010 запускается патч из версии сборки blue0011, то у него проблем с реестром нет
 * (работает с текущим, потом переименование вручную), но есть проблемы с путями до файлов шаблонов генератора кода, Пути нужно
 * указывать руками относительно корня кластера с учетом сборки.
 */
class PatchInstaller
{
    /**
     * Экземпляр файла установки патча.
     *
     * @var null|PatchPrototype
     */
    protected $oPatch;

    /**
     * Имя файла патча.
     *
     * @var string
     */
    protected $sPatchFile = '';

    public function __construct($sPatchFile)
    {
        if (!is_file($sPatchFile)) {
            throw new UpdateException("Patch install error: Patch file not found [{$sPatchFile}]!");
        }
        $this->sPatchFile = basename($sPatchFile);

        require_once $sPatchFile;
        /* Создать экземпляр*/

        $sPatchClass = 'Patch' . mb_substr($this->sPatchFile, 0, mb_strpos($this->sPatchFile, '.'));

        if (!class_exists($sPatchClass)) {
            throw new UpdateException("Patch class not found [{$sPatchClass}]!");
        }
        $this->oPatch = new $sPatchClass();

        /* Проверка на правильность формата */
        if (!($this->oPatch instanceof PatchPrototype)) {
            throw new UpdateException('Patch install error: Patch has invalid format');
        }

        return true;
    }

    // func

    /**
     * Запускает установку обновления.
     *
     * @throws ServerErrorHttpException
     * @throws \Exception
     *
     * @return array|bool
     */
    public function install()
    {
        /* Проверить был ли установлен данный патч до текущего момента */
        if (Api::alreadyInstalled($this->sPatchFile)) {
            throw new ServerErrorHttpException('Patch ' . (int) $this->sPatchFile . ' already installed.');
        }
        /* Установить патч */

        $fileName = $this->sPatchFile;
        $fileName = explode('.', $fileName);
        $fileName = $fileName[0];

        Logger::dump('Patch ' . $fileName . ' install started  At ' . date('r'));
        try {
            if (((int) PHP_VERSION < 7)) {
                throw new \Exception('CanapeCMS 4.03 не работает на PHP ниже 7 версии');
            }
            $mResult = $this->oPatch->execute();
        } catch (\Exception $e) {
            Logger::dump('Patch ' . $fileName . ' install --FAILED-- At ' . date('r'));
            throw $e;
        } finally {
            if ($this->getMessages()) {
                Logger::dump(
                    'Patch ' . $fileName . ' message At ' . date('r') . "\n    " .
                    implode("\n    ", $this->getMessages())
                );
            }
        }

        if ($mResult === null) {
            $mResult = true;
        }

        Logger::dump('Patch ' . $fileName . ' install complete At ' . date('r'));

        if ($this->oPatch->bUpdateCache) {
            \Yii::$app->rebuildRegistry();
            \Yii::$app->rebuildLang();
            \Yii::$app->rebuildCss();
            \Yii::$app->clearParser();
        }

        return $mResult;
    }

    // func

    /**
     * Возвращает описание патча, если таковое присутствует
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->oPatch->sDescription;
    }

    // func

    /**
     * Отдает набор сообщений ои патча после установки.
     *
     * @return string[]
     */
    public function getMessages()
    {
        return $this->oPatch->getMessages();
    }
}// class
