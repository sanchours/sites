<?php
/**
 * Created by PhpStorm.
 * User: ilya
 * Date: 19.03.14
 * Time: 12:45.
 */

namespace unit\build\Adm\Gallery;

use skewer\build\Adm\Gallery\TaskDelOldSourcePhoto;
use skewer\components\gallery\models\Albums;
use skewer\components\gallery\models\Photos;

class GalleryAdmServiceTest extends \Codeception\Test\Unit
{
    /**
     * @covers \skewer\build\Adm\Gallery\TaskDelOldSourcePhoto::execute
     *
     * @throws \Exception
     * @throws \yii\base\UserException
     */
    public function testDelOldSource()
    {
        $oAlbum = new Albums();
        $oAlbum->section_id = 3;
        $oAlbum->profile_id = 1;
        $this->assertTrue($oAlbum->save());

        // добавить запись от сегодня + файл
        $sDir = 'files/gallery_test/';
        $sDirName = WEBPATH . $sDir;
        if (!is_dir($sDirName)) {
            mkdir($sDirName);
        }
        if (!is_dir($sDirName)) {
            $this->fail("Cannot create [{$sDir}] folder");
        }

        $sFileNameToday = $sDir . 'today.png';
        $h = fopen(WEBPATH . $sFileNameToday, 'w+');
        fclose($h);
        $this->assertFileExists(WEBPATH . $sFileNameToday);

        $oPhoto = new Photos();
        $oPhoto->source = $sFileNameToday;
        $oPhoto->creation_date = date('Y-m-d H:i:s');
        $oPhoto->album_id = $oAlbum->id;

        $this->assertTrue($oPhoto->save());
        $iTodayId = $oPhoto->id;

        // от недели назад + файл
        $sFileNameWeek = $sDir . 'Week.png';
        $h = fopen(WEBPATH . $sFileNameWeek, 'w+');
        fclose($h);
        $this->assertFileExists(WEBPATH . $sFileNameWeek);

        $oPhoto = new Photos();
        $oPhoto->source = $sFileNameWeek;
        $oPhoto->creation_date = date('Y-m-d H:i:s', strtotime('-8 days'));
        $oPhoto->album_id = $oAlbum->id;

        $this->assertTrue($oPhoto->save());
        $iWeekId = $oPhoto->id;

        // от месяца назад + файл
        $sFileNameMonth = $sDir . 'Month.png';
        $h = fopen(WEBPATH . $sFileNameMonth, 'w+');
        fclose($h);
        $this->assertFileExists(WEBPATH . $sFileNameMonth);

        $oPhoto = new Photos();
        $oPhoto->source = $sFileNameMonth;
        $oPhoto->creation_date = date('Y-m-d H:i:s', strtotime('-30 days'));
        $oPhoto->album_id = $oAlbum->id;

        $this->assertTrue($oPhoto->save());
        $iMonthId = $oPhoto->id;

        // запуск
        $oTask = new TaskDelOldSourcePhoto();
        $oTask->init();
        $oTask->execute();
        $oTask->complete();

        // сегодняшняя есть
        $this->assertNotEmpty(Photos::findOne($iTodayId));
        $this->assertFileExists(WEBPATH . $sFileNameToday);

        // недельной нет
        $this->assertNotEmpty(Photos::findOne($iWeekId));
        $this->assertFileNotExists(WEBPATH . $sFileNameWeek);

        // месячной нет
        $this->assertNotEmpty(Photos::findOne($iMonthId));
        $this->assertFileNotExists(WEBPATH . $sFileNameMonth);

        unlink(WEBPATH . $sFileNameToday);
        rmdir($sDirName);
        if (is_dir($sDirName)) {
            $this->fail("Cannot remove [{$sDir}] folder");
        }
    }
}
