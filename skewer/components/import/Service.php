<?php

namespace skewer\components\import;

use skewer\base\section\Tree;
use skewer\base\site\ServicePrototype;

class Service extends ServicePrototype
{
    /**
     * @var int количество дней для удаления
     */
    public static $days = 1;

    /**
     * Удаляет старые файлы импорта.
     */
    public static function removeOldFiles()
    {
        $sIdSection = Tree::getSectionByAlias('Tool_Import', \Yii::$app->sections->library());
        $sPath = WEBPATH . "files/{$sIdSection}/";
        $dir = scandir($sPath);
        if ($dir) {
            $time = time() - (self::$days) * 24 * 60 * 60;
            foreach ($dir as $name) {
                $name = $sPath . $name;
                if (is_file($name) == true) {
                    $ftime = filemtime($name);
                    if ($ftime < $time) {
                        unlink($name);
                    }
                }
            }

            return true;
        }

        return false;
    }
}
