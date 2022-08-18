<?php

namespace skewer\build\Adm\Gallery;

use skewer\base\queue;
use skewer\components\gallery\Photo;

/**
 * Задача очистки сайта от старых source-фото
 * Class TaskDelOldSourcePhoto.
 */
class TaskDelOldSourcePhoto extends queue\Task
{
    public $iLimit = 100;

    /**
     * Чистка исходных изображений фотогалерей. Используется в кроне.
     *
     * @throws \Exception
     * @throws \yii\base\UserException
     *
     * @return bool
     */
    public function execute()
    {
        $aImages = Photo::getOlderPhotoWithSourse($this->iLimit);

        // Удаление исходного файла фотографии, если прошла неделя
        foreach ($aImages as $aImage) {
            if ($aImage['source']) {
                // delete source file
                if (file_exists(WEBPATH . $aImage['source'])) {
                    unlink(WEBPATH . $aImage['source']);
                }

                // update DB row
                $aImage['source'] = '';
                Photo::setImage($aImage, $aImage['id']);
            }
        }

        if (count($aImages) < $this->iLimit) {
            $this->setStatus(static::stComplete);

            return true;
        }

        $this->setStatus(static::stInterapt);

        return true;
    }
}
