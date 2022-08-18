<?php

namespace skewer\build\Tool\Rss;

use skewer\base\log\Logger;
use skewer\base\queue;

/**
 * Задача на построение RSS ленты.
 */
class Task extends queue\Task
{
    /**
     * @var string Шаблон Rss фида
     */
    public $sTemplate = 'rss_template.php';

    public function execute()
    {
        $aItems = Api::getRssContent();

        $sContent = \Yii::$app->getView()->renderFile(__DIR__ . '/templates/' . $this->sTemplate, ['aItems' => $aItems]);

        try {
            if (!file_exists(Api::getDirRss())) {
                if (!mkdir(Api::getDirRss())) {
                    $this->setStatus(self::stError);

                    return false;
                }

                chmod(Api::getDirRss(), 0775);
            }

            if (!$handle = fopen(Api::getDirRss() . Api::FILENAME_RSS, 'w+')) {
                $this->setStatus(self::stError);

                return false;
            }

            if (fwrite($handle, $sContent) === false) {
                $this->setStatus(self::stError);

                return false;
            }

            fclose($handle);
        } catch (\Exception $e) {
            Logger::dumpException($e);
            $this->setStatus(self::stError);

            return false;
        }

        $this->setStatus(static::stComplete);

        return true;
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
            'title' => 'update rss',
            'class' => self::className(),
            'priority' => queue\Task::priorityHigh,
        ];
    }
}
