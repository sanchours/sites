<?php

namespace skewer\components\Exchange;

use skewer\base\log\Logger;
use skewer\base\queue\Task;
use skewer\components\garbage\SafeExitFromRecursionException;
use yii\base\ErrorException;

/**
 * Class DeleteFileTask - Задача удаления файлов, загруженных через автоматический обмен из 1с
 */
class DeleteFileTask extends Task
{
    /**
     * Количество файлов, удаляемых за одну итерацию.
     *
     * @var int
     */
    protected $iCountFilesPerIteration = 10;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $iCounterFiles = 1;

        try {
            $this->deleteOldFiles(ExchangeGoods::get1cDir(), function ($item) use (&$iCounterFiles) {
                if ($iCounterFiles++ >= $this->iCountFilesPerIteration) {
                    throw new SafeExitFromRecursionException();
                }
            });

            $this->setStatus(self::stComplete);
        } catch (ErrorException $e) {
            // возникла ошибка
            Logger::dumpException($e);
            $this->setStatus(self::stError);
        } catch (SafeExitFromRecursionException $e) {
            // итерация окончена
        }
    }

    /**
     * Рекурсивное удаление файлов из директории старше $this->iCountExpiredDays дней.
     *
     * @param $dir - директория с файлами
     * @param callable $afterUnlink - действие после удаления
     *
     * @throws ErrorException
     */
    private function deleteOldFiles($dir, callable $afterUnlink)
    {
        if (!is_dir($dir)) {
            throw new ErrorException("'{$dir}' не является директорией");
        }
        if (!is_readable($dir) || !is_writable($dir)) {
            throw new ErrorException("Не верные права доступа на директорию '{$dir}'");
        }
        $handle = opendir($dir);

        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . \DIRECTORY_SEPARATOR . $file;

            if (is_dir($path)) {
                $this->deleteOldFiles($path, $afterUnlink);
            } else {
                unlink($path);
                call_user_func_array($afterUnlink, ['item' => $path]);
            }
        }

        closedir($handle);

        if (is_link($dir)) {
            @unlink($dir);
        } else {
            @rmdir($dir);
        }
    }

    /**
     * Получить имя класса.
     *
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * Получить конфиг задачи.
     *
     * @return array
     */
    public static function getConfig()
    {
        return [
            'class' => self::className(),
            'priority' => Task::priorityHigh,
            'resource_use' => Task::weightNormal,
            'title' => 'Удаление файлов импорта',
            'parameters' => [],
        ];
    }
}
