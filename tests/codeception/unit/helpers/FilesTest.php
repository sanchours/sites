<?php
/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 26.05.2016
 * Time: 10:29.
 */

namespace unit\helpers;

use skewer\helpers\Files;
use yii\helpers\FileHelper;

class FilesTest extends \Codeception\Test\Unit
{
    public function providerCreateFolderPath()
    {
        return [
            ['testgallery/87/sources', false],
            ['testgallery/87/sources/', false],
            ['testgallery/87/sources', true],
            ['testgallery/87/sources/', true],
        ];
    }

    /**
     * @covers \skewer\helpers\Files::createFolderPath
     * @dataProvider providerCreateFolderPath
     *
     * @param mixed $sPath
     * @param mixed $bProtected
     */
    public function testCreateFolderPathCreate($sPath, $bProtected = false)
    {
        // инициализация файлов
        Files::init(FILEPATH, PRIVATE_FILEPATH);
        $sRootFolder = (!$bProtected) ? FILEPATH : PRIVATE_FILEPATH;
        $this->assertSame($sRootFolder . $sPath, Files::createFolderPath($sPath, $bProtected), "[{$sPath}]");
        $aPath = explode('/', $sPath);
        FileHelper::removeDirectory($sRootFolder . $aPath[0]);
    }

    /**
     * @covers \skewer\helpers\Files::createFolderPath
     * @dataProvider providerCreateFolderPath
     *
     * @param mixed $sPath
     * @param mixed $bProtected
     */
    public function testCreateFolderPathUpload($sPath, $bProtected = false)
    {
        // инициализация файлов
        Files::init(FILEPATH, PRIVATE_FILEPATH);
        $sRootFolder = (!$bProtected) ? FILEPATH : PRIVATE_FILEPATH;
        Files::createFolderPath($sPath, $bProtected);
        $this->assertSame($sRootFolder . $sPath, Files::createFolderPath($sPath, $bProtected), "[{$sPath}]");
        $aPath = explode('/', $sPath);
        FileHelper::removeDirectory($sRootFolder . $aPath[0]);
    }
}
