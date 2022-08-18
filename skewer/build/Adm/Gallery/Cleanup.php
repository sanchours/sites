<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 13.09.2018
 * Time: 10:41.
 */

namespace skewer\build\Adm\Gallery;

use skewer\components\cleanup\CleanupPrototype;
use skewer\components\cleanup\CleanupScanFiles;
use skewer\components\gallery\models\Albums;
use skewer\components\gallery\Photo;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use yii\helpers\ArrayHelper;

class Cleanup extends CleanupPrototype
{
    public function getData()
    {
        $aData = $this->scanDb();

        return $aData;
    }

    /**
     * @return array
     */
    private function scanDb()
    {
        //запросим все альбомы
        $oAlbums = Albums::find()->all();
        $oAlbums = ArrayHelper::index($oAlbums, 'id');

        $aPhotos = [];

        /** @var Albums $oAlbum */
        foreach ($oAlbums as $oAlbum) {
            $aPhotoFromAlbum = Photo::getFromAlbum($oAlbum->id);
            if (is_array($aPhotoFromAlbum)) {
                $aPhotos = array_merge($aPhotos, $aPhotoFromAlbum);
            }
        }

        $aData = [];
        /** @var Photo $oPhoto */
        foreach ($aPhotos as $oPhoto) {
            if ($oPhoto['thumbnail']) {
                $aData[] = $this->formatDataScanDb($oPhoto['thumbnail'], $oAlbums[$oPhoto['album_id']]);
            }
            if ($oPhoto['source']) {
                $aData[] = $this->formatDataScanDb($oPhoto['source'], $oAlbums[$oPhoto['album_id']]);
            }
            $aFilesData = array_values(ArrayHelper::getColumn($oPhoto['images_data'], 'file'));
            if (count($aFilesData)) {
                foreach ($aFilesData as $file) {
                    if ($file) {
                        $aData[] = $this->formatDataScanDb($file, $oAlbums[$oPhoto['album_id']]);
                    }
                }
            }
        }

        return $aData;
    }

    /**
     * @return array
     */
    public function scanFiles()
    {
        $oDirectoryIterator = new RecursiveDirectoryIterator(FILEPATH, RecursiveDirectoryIterator::SKIP_DOTS);

        $aFilesData = $this->scanFilesGallery($oDirectoryIterator);

        $aData = [];

        foreach ($aFilesData as $item) {
            $aData[] = $this->formatDataScanFiles($item);
        }

        return $aData;
    }

    /**
     * @param RecursiveDirectoryIterator $oDirectoryIterator
     *
     * @return array
     */
    private function scanFilesGallery($oDirectoryIterator)
    {
        $aData = [];
        if ($oDirectoryIterator->valid()) {
            do {
                if ($oDirectoryIterator->getFilename() == 'gallery') {
                    if ($oDirectoryIterator->hasChildren()) {
                        $aResultsScan = CleanupScanFiles::recursiveScanFiles($oDirectoryIterator->getChildren());
                        $aData = array_merge($aData, $aResultsScan);
                    } else {
                        $aData[] = $oDirectoryIterator->getPathname();
                    }
                }

                if ($oDirectoryIterator->hasChildren()) {
                    $aResultsScan = $this->scanFilesGallery($oDirectoryIterator->getChildren());
                    $aData = array_merge($aData, $aResultsScan);
                }
                $oDirectoryIterator->next();
            } while ($oDirectoryIterator->valid());
        }

        return $aData;
    }

    private function unificationPath($path, $action)
    {
        if ($action == 'scanDb') {
            $path = 'web' . $path;
        }

        if ($action == 'scanFiles') {
            $path = str_replace(ROOTPATH, '', $path);
        }

        return $path;
    }

    /**
     * @param $file
     * @param Albums $album
     *
     * @return array|bool
     */
    public function formatDataScanDb($file, Albums $album)
    {
        if (empty($file)) {
            return false;
        }

        $aData = $this->getFormatDataScanDb();
        $aData['module'] = self::className();
        $aData['file'] = $this->clearDoubleSlach(
            $this->unificationPath($file, 'scanDb')
        );
        $aData['assoc_data_storage'] = json_encode([
            'album' => $album->id,
            'section_id' => $album->section_id,
            'owner' => $album->owner,
            'visible' => $album->visible,
            'profile_id' => $album->profile_id,
        ]);

        return $aData;
    }

    /**
     * @param $file
     *
     * @return array|bool
     */
    public function formatDataScanFiles($file)
    {
        if (empty($file)) {
            return false;
        }

        $aData = $this->getFormatDataScanFiles();
        $aData['module'] = self::className();
        $aData['file'] = $this->clearDoubleSlach(
            $this->unificationPath($file, 'scanFiles')
        );

        return $aData;
    }
}
