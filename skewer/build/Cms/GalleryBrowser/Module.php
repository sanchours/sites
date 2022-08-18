<?php

namespace skewer\build\Cms\GalleryBrowser;

use skewer\base\site_module\Context;
use skewer\build\Cms;
use skewer\components\gallery;
use skewer\components\gallery\Profile;

/**
 * Модуль для отображения галереи
 * Подчиненные модули:
 * Панель с файлами из основного интерфейса
 * Class Module.
 */
class Module extends Cms\Frame\ModulePrototype
{
    protected function actionInit()
    {
        // подключаем модули
        $this->addChildProcess(new Context(
            'files',
            'skewer\build\Adm\Gallery\Module',
            ctModule,
            [
                'iCurrentAlbumId' => 0,
                'onlyAlbumEditor' => true,
                'popup' => true,
            ]
        ));

        $this->setCmd('findAlbum');
    }

    protected function actionShowSection()
    {
        // подключаем модули
        $this->addChildProcess(new Context(
            'files',
            'skewer\build\Adm\Gallery\Module',
            ctModule,
            [
                'sectionId' => $this->get('sectionId'),
            ]
        ));

        $this->setCmd('init');
    }

    protected function actionShowAlbum()
    {
        $iAlbumId = $this->get('gal_album_id');
        $sSeoClass = $this->get('seoClass');
        $iEntityId = $this->get('iEntityId', 0);
        $iSectionId = $this->get('sectionId', 0);

        try {
            //Пытаемся получить profile_id из GET
            $iProfileId = $this->get('gal_profile_id');

            /*
             * При добавлении альбома через диз режим, т.е. когда поле галерея указанно как css параметр
             * Если нет пытаемся получить по id альбома
             */
            if (!$iProfileId && $iAlbumId) {
                if (is_numeric($iAlbumId)) {
                    $oAlbum = gallery\models\Albums::find()
                        ->where(['id' => $iAlbumId])
                        ->one();
                    $iProfileId = $oAlbum->profile_id;
                } elseif (is_string($iAlbumId)) {
                    // через $iAlbumId передаём тип профиля
                    $iAlbumId = null;
                    $iProfileId = Profile::getDefaultId(Profile::TYPE_FAVICON);
                }
            }

            /*Если нет. берем дефолтный*/
            if (!$iProfileId) {
                $iProfileId = Profile::getDefaultId(Profile::TYPE_CATALOG);
            }

            // если альбом еще не создан для данного объекта
            if (!$iAlbumId) {
                $iAlbumId = gallery\Album::setAlbum([
                                                         'owner' => 'entity',
                                                         'profile_id' => $iProfileId,
                                                         'section_id' => 0,
                                                    ]);
            } elseif ($this->get('gal_new_album')) {
                // Очистить альбом и изменить профиль галереи
                gallery\Album::clearAlbum($iAlbumId);
                if ($iProfileId) {
                    gallery\Album::setAlbum(['profile_id' => $iProfileId], $iAlbumId);
                }
            }

            /** Параметры модуля галереи для перестроения интерфейса */
            $aModuleParams = [
                'iCurrentAlbumId' => $iAlbumId,
                'onlyAlbumEditor' => true,
                'popup' => true,
                'iEntityId' => $iEntityId,
                'sSeoClass' => $sSeoClass,
                'sectionId' => $iSectionId,
            ];

            $this->setData('album', $iAlbumId);
        } catch (\Exception $e) {
            $aModuleParams = [
                'sErrorText' => $e->getMessage(),
            ];
        }

        $this->addChildProcess(new Context('files', 'skewer\build\Adm\Gallery\Module', ctModule, $aModuleParams));
        $this->setData('album', $iAlbumId);
        $this->setCmd('showAlbum');
    }
}
