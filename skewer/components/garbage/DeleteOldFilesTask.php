<?php

namespace skewer\components\garbage;

use skewer\base\log\Logger;
use skewer\base\queue\Task;
use yii\base\ErrorException;

/**
 * Class DeleteOldFilesTask.
 */
class DeleteOldFilesTask extends Task
{
    /**
     * Количество файлов, удаляемых за одну итерацию.
     *
     * @var int
     */
    protected $iCountFilesPerIteration = 10;

    /**
     * Время хранения файлов(в днях).
     *
     * @var int
     */
    protected $iCountExpiredDays = 30;

    public function setCountFilesPerIteration($iCount)
    {
        $this->iCountFilesPerIteration = $iCount;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $iCounterFiles = 1;

        try {
            $this->deleteOldFiles(Garbage::getDir(), function ($item) use (&$iCounterFiles) {
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
        // нет директории - выходим сразу
        if (!is_dir($dir)) {
            return;
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
                $Interval = date_diff(new \DateTime(), \DateTime::createFromFormat(Garbage::DIR_FORMAT, $file));
                if ($Interval->days > $this->iCountExpiredDays) {
                    $this->deleteOldFiles($path, $afterUnlink);
                }
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
     * Вернет имя класса.
     *
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }
}
