<?php

namespace unit\components\gallery;

use skewer\base\section\Tree;
use skewer\components\gallery\Album;
use skewer\components\gallery\Format;
use skewer\components\gallery\Photo;
use skewer\components\gallery\Profile;

/**
 * @covers \skewer\components\gallery\Album
 * @covers \skewer\components\gallery\Photo
 * @covers \skewer\components\gallery\Profile
 * @covers \skewer\components\gallery\Format
 * Комплексный тест компонента галереи
 */
class ComplexTest extends \Codeception\Test\Unit
{
    const TMP_DIR = 'files/gallery_test/'; // Временная директория для теста
    const TMP_ALBUM_ALIAS = 'testalbum231'; // alias тестового альбома
    const TMP_PROFILE_ALIAS = 'testprofile231'; // alias тестового профиля
    public static $TEST_FORMATS = [ // Тестовые форматы
        'min-test' => [
            'width' => 101,
            'height' => 100,
            'active' => 1,
        ],
        'med-test' => [
            'width' => 401,
            'height' => 400,
            'active' => 0,
        ],
    ];

    public static $iProfileId; // Id тестового профиля
    public static $iSectionId; // Id тестовой секции
    public static $iAlbumId; // Id тестового фльбома

    /**
     * Базовый тест компонента.
     */
    public function testBase()
    {
        $this->SectionTest(); // Создать/протестировать секцию
        $this->ProfileTest(); // Создать/протестировать профиль
        $this->FormatsTest(); // Создать/протестировать форматы
        $this->AlbumTest(); // Создать/протестировать альбом
        $this->PhotoTest(); // Создать/протестировать изображение

        $this->PhotoTest(true); // Удалить/протестировать изображение
        $this->AlbumTest(true); // Удалить/протестировать альбом
        $this->FormatsTest(true); // Удалить/протестировать форматы
        $this->ProfileTest(true); // Удалить/протестировать профиль
        $this->SectionTest(true); // Удалить/протестировать секцию
    }

    /**
     * Проверка удаления альбома с разделом
     */
    public function testRemoveSection()
    {
        $this->SectionTest(); // Создать/протестировать секцию
        $this->ProfileTest(); // Создать/протестировать профиль
        $this->AlbumTest(); // Создать/протестировать альбом

        $this->SectionTest(true); // Удалить/протестировать секцию
        $this->assertEmpty(Album::getBySection(self::$iSectionId), 'Альбом с секцией не удалился');

        $this->AlbumTest(true); // Удалить/протестировать альбом
        $this->ProfileTest(true); // Удалить/протестировать профиль
    }

    /**
     * Проверка удаления изображений с альбомом
     */
    public function testRemovePhotosWithAlbom()
    {
        $this->SectionTest(); // Создать/протестировать секцию
        $this->ProfileTest(); // Создать/протестировать профиль
        $this->FormatsTest(); // Создать/протестировать форматы
        $this->AlbumTest(); // Создать/протестировать альбом
        $this->PhotoTest(); // Создать/протестировать изображение

        $aPhotos = Photo::getFromAlbum(self::$iAlbumId);
        $iPhotoId = $aPhotos[0]->id;

        $this->AlbumTest(true); // Удалить/протестировать альбом

        $this->assertEmpty(Photo::getImage($iPhotoId), 'Изображение не удалилась с альбомом');

        $this->PhotoTest(true); // Удалить/протестировать изображение и картинки физически
        $this->FormatsTest(true); // Удалить/протестировать форматы
        $this->ProfileTest(true); // Удалить/протестировать профиль
        $this->SectionTest(true); // Удалить/протестировать секцию
    }

    /**
     * Проверка удаления форматов с профилем
     */
    public function testRemoveFormatsWithProfile()
    {
        $this->SectionTest(); // Создать/протестировать секцию
        $this->ProfileTest(); // Создать/протестировать профиль
        $this->FormatsTest(); // Создать/протестировать форматы

        $aFormats = Format::getByProfile(self::$iProfileId);

        $this->ProfileTest(true); // Удалить/протестировать профиль

        foreach ($aFormats as $aFormat) {
            $this->assertEmpty(Format::getById($aFormat['id']), 'Формат не удалился с профилем');
        }

        $this->SectionTest(true); // Удалить/протестировать секцию
    }

    /**
     * Проверка выбора профиля по умолчанию.
     *
     * @covers \skewer\components\gallery\Profile::setDefaultProfile
     */
    public function testChangeDefProfile()
    {
        $this->ProfileTest(); // Создать/протестировать профиль

        // Создать ещё два профиля
        $iProfileId2 = Profile::setProfile([
            'title' => 'Тестовый профиль 2',
            'alias' => self::TMP_PROFILE_ALIAS,
            'type' => Profile::TYPE_SECTION,
            'active' => 1,
        ]);
        $iProfileId3 = Profile::setProfile([
            'title' => 'Тестовый профиль 3',
            'alias' => self::TMP_PROFILE_ALIAS,
            'type' => Profile::TYPE_SECTION,
            'active' => 1,
        ]);
        $iProfileId4 = Profile::setProfile([
            'title' => 'Тестовый профиль 4',
            'alias' => self::TMP_PROFILE_ALIAS,
            'type' => Profile::TYPE_CATALOG,
            'active' => 1,
        ]);
        $this->assertNotEmpty($iProfileId2 and $iProfileId3 and $iProfileId4, 'Не удалось добавить профили');

        Profile::setDefaultProfile($iProfileId2); // Установить по умолчанию для секции
        Profile::setDefaultProfile($iProfileId4); // Установить по умолчанию для каталога

        if (Profile::getDefaultId(Profile::TYPE_SECTION) != $iProfileId2) {
            $this->fail('Профиль секции не установился по умолчанию');
        }
        if (Profile::getDefaultId(Profile::TYPE_CATALOG) != $iProfileId4) {
            $this->fail('Профиль каталога не установился по умолчанию');
        }

        $aProfile = Profile::getById(self::$iProfileId);
        $this->assertEmpty($aProfile['default'], 'Лишний профиль установлен по умолчанию 1');
        $aProfile = Profile::getById($iProfileId3);
        $this->assertEmpty($aProfile['default'], 'Лишний профиль установлен по умолчанию 2');

        Profile::removeProfile($iProfileId4);
        Profile::removeProfile($iProfileId3);
        Profile::removeProfile($iProfileId2);

        $this->ProfileTest(true); // Создать/протестировать профиль
    }

    // ****************************************************************************************************************************************
    // ******************************************** ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ ********************************************************************
    // ****************************************************************************************************************************************

    /** Работа с временной секцией
     * @covers \skewer/base/section/Tree::addSection
     * @covers \skewer/base/section/Tree::removeSection
     *
     * @param mixed $bRemove
     */
    private function SectionTest($bRemove = false)
    {
        if (!$bRemove) { // Создание
            $oSection = Tree::addSection(\Yii::$app->sections->topMenu(), 'gallery');
            $this->assertNotEmpty($oSection, 'Не удалось создать секцию');
            self::$iSectionId = $oSection->id;
        } else { // Удаление
            $this->assertNotEmpty(Tree::removeSection(self::$iSectionId), 'Не удалось удалить временную секцию');
            self::$iSectionId = 0;
        }
    }

    /** Работа с временным профилем
     *  @covers \skewer/components/gallery/Profile::setProfile
     *  @covers \skewer/components/gallery/Profile::removeProfile
     *
     * @param mixed $bRemove
     */
    private function ProfileTest($bRemove = false)
    {
        if (!$bRemove) { // Создание
            if ($aProfile = Profile::getByAlias(self::TMP_PROFILE_ALIAS)) {
                self::$iProfileId = $aProfile['id'];
            } else {
                self::$iProfileId = 0;
            }

            self::$iProfileId = Profile::setProfile([
                'title' => 'Тестовый профиль',
                'alias' => self::TMP_PROFILE_ALIAS,
                'type' => Profile::TYPE_SECTION,
                'active' => 0,
                'default' => 1,
            ], self::$iProfileId);
            $this->assertNotEmpty(self::$iProfileId, 'Не удалось создать профиль галереи');
            $this->assertNotEmpty($aProfile = Profile::getByAlias(self::TMP_PROFILE_ALIAS), 'Не удалось получить профиль по псевдониму');
            $this->assertNotEmpty(is_array($aProfile) and isset($aProfile['id']), 'Профиль вернулся не как массив 1');
            $this->assertNotEmpty($aProfile = Profile::getById(self::$iProfileId), 'Не удалось получить профиль по id');
            $this->assertNotEmpty(is_array($aProfile) and isset($aProfile['id']), 'Профиль вернулся не как массив 2');
            $this->assertEmpty($aProfile['default'], 'Присвоение профиля по умолчание не должно осуществлять в методе setProfile()');

            $aProfiles = Profile::getActiveByType(Profile::TYPE_SECTION, true);
            $this->assertEmpty(isset($aProfiles[self::$iProfileId]), 'Не активныей профиль не должен возвращаться в getActiveByType()');
            Profile::setProfile(['active' => 1], self::$iProfileId); // Установить активность
            $aProfiles = Profile::getActiveByType(Profile::TYPE_SECTION, true);
            $this->assertNotEmpty(isset($aProfiles[self::$iProfileId]), 'Активный профиль не возвратился в getActiveByType()');
        } else { // Удаление
            Profile::removeProfile(self::$iProfileId);
            $this->assertEmpty(Profile::getById(self::$iProfileId), 'Не удалось удалить профиль');
            self::$iProfileId = 0;
        }
    }

    /** Работа с временными форматами
     * @covers \skewer/components/gallery/Format::getByName
     * @covers \skewer/components/gallery/Format::setFormat
     * @covers \skewer/components/gallery/Format::getById
     * @covers \skewer/components/gallery/Format::getByProfile
     * @covers \skewer/components/gallery/Format::removeFormat
     *
     * @param mixed $bRemove
     */
    private function FormatsTest($bRemove = false)
    {
        if (!$bRemove) { // Создание
            if (!self::$iProfileId) {
                $this->fail('Тестовый профиль отсутствует');
            }

            $iFormatId = 0;

            foreach (self::$TEST_FORMATS as $sName => $aData) { // Создать тестовые форматы
                if ($aFormats = Format::getByName($sName, self::$iProfileId)) {
                    $iFormatId = $aFormats[0]['id'];
                } else {
                    $iFormatId = 0;
                }

                $iFormatId = Format::setFormat(array_merge([
                    'profile_id' => self::$iProfileId,
                    'title' => 'Тестовый формат ' . $sName,
                    'name' => $sName,
                ], $aData), $iFormatId);
                $this->assertNotEmpty($iFormatId, 'Не удалось создать формат');
            }
            reset(self::$TEST_FORMATS);
            $this->assertNotEmpty($aFormats = Format::getByName(key(self::$TEST_FORMATS), self::$iProfileId), 'Не удалось получить формат по имени');
            $this->assertNotEmpty(is_array($aFormats[0]) and isset($aFormats[0]['id']), 'Формат вернулся не как массив 1');
            $this->assertNotEmpty($oFormat = Format::getById($iFormatId), 'Не удалось получить формат по Id');
            $this->assertNotEmpty(isset($oFormat->id), 'Формат вернулся не как объект 2');

            // Проверить получение только активных форматов
            $aFormats = Format::getByProfile(self::$iProfileId, true);
            foreach (self::$TEST_FORMATS as $sName => $aData) {
                $bFound = false;
                foreach ($aFormats as $aFormat) {
                    if ($aFormat['name'] === $sName) {
                        $this->assertEmpty(empty($aData['active']), 'Не активный формат в списке аклтивных');
                        $bFound = true;
                        break;
                    }
                }
                $this->assertNotEmpty(($bFound or empty($aData['active'])), 'Не верно получен список форматов профиля');
                if (empty($aData['active'])) { // Попутно установить активность не активным тестовым форматам
                    $aFormat = Format::getByName($sName, self::$iProfileId);
                    $this->assertNotEmpty(count($aFormat) == 1, 'Не верно получен не активный форма');
                    Format::setFormat(['active' => 1], $aFormat[0]['id']);
                }
            }
        } else { // Удаление
            foreach (self::$TEST_FORMATS as $sName => $aData) {
                $aFormat = Format::getByName($sName, self::$iProfileId);
                $this->assertNotEmpty(count($aFormat) == 1, 'Не найден формат ' . $sName);
                Format::removeFormat($aFormat[0]['id']);
                $this->assertEmpty(Format::getByName($sName, self::$iProfileId), 'Не удалился формат ' . $sName);
            }
        }
    }

    /** Работа с временным альбомом
     * @covers \skewer/components/gallery/Album::getByAlias
     * @covers \skewer/components/gallery/Album::setAlbum
     * @covers \skewer/components/gallery/Album::getByAlias
     * @covers \skewer/components/gallery/Album::getById
     * @covers \skewer/components/gallery/Album::getBySection
     * @covers \skewer/components/gallery/Album::toggleActiveAlbum
     * @covers \skewer/components/gallery/Album::removeAlbum
     *
     * @param mixed $bRemove
     */
    private function AlbumTest($bRemove = false)
    {
        if (!$bRemove) { // Создание
            if (!self::$iSectionId) {
                $this->fail('Тестовая секция отсутствует');
            }
            if (!self::$iProfileId) {
                $this->fail('Тестовый профиль отсутствует');
            }

            if ($aAlbum = Album::getByAlias(self::TMP_ALBUM_ALIAS)) {
                self::$iAlbumId = $aAlbum['id'];
            } else {
                self::$iAlbumId = 0;
            }
            self::$iAlbumId = Album::setAlbum([
                'section_id' => self::$iSectionId,
                'profile_id' => self::$iProfileId,
                'alias' => self::TMP_ALBUM_ALIAS,
                'title' => 'Временный тестовый альбом',
                'visible' => 0,
            ], self::$iAlbumId);
            $this->assertNotEmpty(self::$iAlbumId, 'Не удалось создать альбом');

            // Проверить доступность альбома
            $this->assertNotEmpty($aAlbum = Album::getByAlias(self::TMP_ALBUM_ALIAS), 'Не удалось получить альбом по alias');
            $this->assertNotEmpty(is_array($aAlbum) and isset($aAlbum['id']), 'Альбом вернулся не как массив 1');
            $this->assertNotEmpty($oAlbum = Album::getById(self::$iAlbumId), 'Не удалось получить альбом по id');
            $this->assertNotEmpty(isset($oAlbum->id), 'Альбом вернулся не как объект');
            $this->assertNotEmpty($aAlbums = Album::getBySection(self::$iSectionId, false), 'Не удалось получить альбом по секции');
            $this->assertNotEmpty(is_array($aAlbums[0]) and isset($aAlbums[0]['id']), 'Альбом вернулся не как массив 2');

            // Проверить получение не видимого и видимого альбомов
            $aAlbums = Album::getBySection(self::$iSectionId);
            foreach ($aAlbums as $aAlbum) {
                if ($aAlbum['alias'] == self::TMP_ALBUM_ALIAS) {
                    $this->fail('Вернулся не активный альбом');
                }
            }
            Album::toggleActiveAlbum(self::$iAlbumId); // Сделать видимым
            $aAlbums = Album::getBySection(self::$iSectionId);
            $bFound = false;
            foreach ($aAlbums as $aAlbum) {
                if ($aAlbum['alias'] == self::TMP_ALBUM_ALIAS) {
                    $bFound = true;
                }
            }
            $this->assertNotEmpty($bFound, 'Не переключилась активность альбома');
        } else { // Удаление
            Album::removeAlbum(self::$iAlbumId);
            $this->assertEmpty(Album::getById(self::$iAlbumId), 'Альбом не удалился');
            self::$iAlbumId = 0;
        }
    }

    /** Работа с временным изображением
     *  @covers \skewer\components\gallery\Photo::processImage()
     *  @covers \skewer\components\gallery\Photo::setImage()
     *  @covers \skewer\components\gallery\Photo::getImage()
     *  @covers \skewer\components\gallery\Photo::getPictures
     *  @covers \skewer\components\gallery\Photo::removeImage()
     *
     * @param mixed $bRemove
     */
    private function PhotoTest($bRemove = false)
    {
        static $sDirName, $iPhotoId, $aImages;
        static $sSource, $sThumbnail;

        if (!$bRemove) { // Создание
            if (!self::$iProfileId) {
                $this->fail('Тестовый профиль отсутствует');
            }
            if (!self::$iAlbumId) {
                $this->fail('Тестовый альбом отсутствует');
            }
            // Создать временную директорию
            $sDir = 'files/gallery_test/';
            $sDirName = WEBPATH . $sDir;
            $sFilename = 'testImage.jpg';
            if (!is_dir($sDirName)) {
                mkdir($sDirName);
            }
            if (!is_dir($sDirName)) {
                $this->fail("Cannot create [{$sDir}] folder");
            }

            // Проверка получения форматов
            $this->assertNotEmpty($aFormats = Format::getByProfile(self::$iProfileId, true), 'Отсутствуют активные форматы изображения у профиля');
            $this->assertNotEmpty(is_array($aFormats[0]) and isset($aFormats[0]['id']), 'Форматы профиля вернулись не как массив');

            // Временное изображение
            $im = imagecreatetruecolor(800, 800);
            imagejpeg($im, $sDirName . $sFilename, 100);
            $aNewImage = Photo::processImage($sDirName . $sFilename, self::$iProfileId, self::$iSectionId, false, true);
            $this->assertNotEmpty($aNewImage, 'Не удалось обработать изображение');

            $sThumbnail = (isset($aNewImage['thumbnail'])) ? $aNewImage['thumbnail'] : '';
            $this->assertNotEmpty($aNewImage, 'Не создалась миниатюра');
            unset($aNewImage['thumbnail']);

            // Добавление изображения в БД
            $iPhotoId = Photo::setImage([
                'visible' => 1,
                'album_id' => self::$iAlbumId,
                'source' => $sDir . $sFilename,
                'thumbnail' => $sThumbnail,
                'images_data' => json_encode($aNewImage),
            ]);
            $this->assertNotEmpty($iPhotoId, 'Не удалось сохранить изображение');

            $oPhoto = Photo::getImage($iPhotoId);
            $this->assertNotEmpty($oPhoto, 'Не найдено созданное изображение');
            $this->assertNotEmpty(isset($oPhoto->id), 'Изображение вернулось не как объект');

            $this->assertNotEmpty(Photo::getCountByAlbum(self::$iAlbumId) == 1, 'Не верно вернулось число изображений в альбоме');

            // Проверка формирования форматов изображения
            $aImages = $oPhoto->getPictures(); // Получить картинки форматов
            $this->assertNotEmpty($aImages, 'Не найдены изображения форматов');
            $this->assertNotEmpty(is_array($aImages), 'Обработанные изображения вернулись не как массив');
            if (count(array_intersect_key($aImages, self::$TEST_FORMATS)) != count(self::$TEST_FORMATS)) {
                $this->fail('Не все форматы изображения были обработаны');
            }
            $this->assertNotEmpty($oPhoto->source, 'Исходное изображение отсутствует в БД');
            $this->assertNotEmpty($oPhoto->thumbnail, 'Миниатюра отсутствует в БД');

            // Физическая проверка файлов форматов изображений
            foreach ($aImages as $sFormstName => $aImg) {
                $this->assertNotEmpty(file_exists(WEBPATH . $aImg['file']), 'Изображение формата отсутствует');

                // Сравнить правильность ресайзинга
                if (!isset(self::$TEST_FORMATS[$sFormstName])) {
                    continue;
                }
                if (!$aImgInfo = getimagesize(WEBPATH . $aImg['file'])) {
                    $this->fail('Не удалось получить информацию о изображении ' . $aImg['file']);
                }
                $aImgData = &self::$TEST_FORMATS[$sFormstName];
                if ($aImgData['width'] != $aImgInfo[0] or $aImgData['height'] != $aImgInfo[1]) {
                    $this->fail('Не верно обработалось изображение ' . $aImg['file']);
                }
            }

            $sSource = $oPhoto->source;
            $sThumbnail = $oPhoto->thumbnail;
            $this->assertNotEmpty(file_exists(WEBPATH . $sSource), 'Исходное изображение отсутствует');
            $this->assertNotEmpty(file_exists(WEBPATH . $sThumbnail), 'Изображение предпросмотра отсутствует');
        } else { // Удаление
            // Удалить изображение
            Photo::removeImage($iPhotoId);
            $this->assertEmpty(Photo::getImage($iPhotoId), 'Изображение не удалилось');

            // Физическая проверка наличия файлов форматов изображений
            foreach ($aImages as $sFormstName => $aImg) {
                $this->assertEmpty(file_exists(WEBPATH . $aImg['file']), 'Изображение формата не удалилось');
            }
            $this->assertEmpty(file_exists(WEBPATH . $sSource), 'Исходное изображение не удалилось');
            $this->assertEmpty(file_exists(WEBPATH . $sThumbnail), 'Изображение предпросмотра не удалилось');

            // Удаление временных директорий
            rmdir($sDirName);
            if (is_dir($sDirName)) {
                $this->fail("Не удалиласб директория {$sDirName}");
            }
        }
    }
}
