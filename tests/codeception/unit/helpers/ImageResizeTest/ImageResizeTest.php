<?php

namespace unit\helpers;

use skewer\helpers\ImageResize;
use yii\helpers\FileHelper;

/**
 * Created by PhpStorm.
 * User: User
 * Date: 27.03.14
 * Time: 10:05.
 *
 * Для запуска  codecept run -c tests/codeception.yml unit tests/codeception/unit/helpers/ImageResizeTest.php -vv
 */
class ImageResizeTest extends \Codeception\Test\Unit
{
    public static $sSourceDir = 'files';

    public static $sTestFile = 'test.jpg';

    public static $iSectionId = 9999999;

    /**
     * @covers \skewer\helpers\ImageResize::getSectionIdFromPath
     * @dataProvider providerGetSectionIdFromPath
     *
     * @param mixed $sIn
     * @param mixed $iOut
     */
    public function testGetSectionIdFromPath($sIn, $iOut)
    {
        $this->assertSame($iOut, ImageResize::getSectionIdFromPath($sIn));
    }

    /**
     * предоствляет данные для теста метода достающего id радела из имени файла.
     *
     * @return array
     */
    public function providerGetSectionIdFromPath()
    {
        return [
            ['/files/123/manul.jpg', 123],
            ['/files/cedas/321/manul.jpg', 321],
            ['/files/cedas/manul.jpg', 0],
            ['/files/cedas/321manul.jpg', 0],
        ];
    }

    public static function getFullImageName()
    {
        $sImgSrc = 'files' . \DIRECTORY_SEPARATOR . self::$iSectionId . \DIRECTORY_SEPARATOR . self::$sTestFile;

        return WEBPATH . $sImgSrc;
    }

    public static function getResizeFileName($sRealName, $iWidth, $iHeight, $sExtension)
    {
        return \DIRECTORY_SEPARATOR . 'files' . \DIRECTORY_SEPARATOR . self::$iSectionId . \DIRECTORY_SEPARATOR . 'resize/' . sprintf('%s_%d_%d.%s', $sRealName, $iWidth, $iHeight, $sExtension);
    }

    public function provider4WrapTags()
    {
        $sImgSrc = 'files' . \DIRECTORY_SEPARATOR . self::$iSectionId . \DIRECTORY_SEPARATOR . self::$sTestFile;
        $iRealW = 320;
        $iRealH = 320;
        $aSizeImage = [
            ['w' => $iRealW, 'h' => $iRealH],
            ['w' => $iRealW - 10, 'h' => $iRealH - 10],
            ['w' => $iRealW, 'h' => $iRealH],
            ['w' => $iRealW - 10, 'h' => $iRealH - 10],
            ['w' => 100, 'h' => 100],
            ['w' => 50, 'h' => 50],
        ];

        $sAddClass = ImageResize::addJsClass;
        $aProvider = [];

        /* #0. Реальные размеры = Пришедшие размеры, нет $sAddClass */
        $aProvider[] = [
                self::$iSectionId,
                "<img alt=\"\" src=\"{$sImgSrc}\" style=\"margin:5px; width: {$aSizeImage[0]['w']}px; height: {$aSizeImage[0]['h']}px;\" />",
                "<img alt=\"\" src=\"{$sImgSrc}\" style=\"margin:5px; width: {$aSizeImage[0]['w']}px; height: {$aSizeImage[0]['h']}px;\" />",
        ];

        // разбираем старое имя
        $sRealName = mb_substr(mb_strrchr(self::getFullImageName(), '/'), 1);
        $sFileExtension = mb_substr(mb_strrchr($sRealName, '.'), 1);
        $sRealName = mb_substr($sRealName, 0, mb_strpos($sRealName, '.'));
        $sNewName = self::getResizeFileName($sRealName, $aSizeImage[1]['w'], $aSizeImage[1]['h'], $sFileExtension);

        /* #1. Реальные размеры <> Пришедшие размеры, нет $sAddClass */
        $aProvider[] = [
                self::$iSectionId,
                "<img alt=\"\" src=\"{$sImgSrc}\" style=\"margin:5px; width: {$aSizeImage[1]['w']}px; height: {$aSizeImage[1]['h']}px;\" />",
                "<a href=\"{$sImgSrc}\" class=\"{$sAddClass}\" data-fancybox=\"button\" ><img alt=\"\" src=\"{$sNewName}\" style=\"margin:5px; width: {$aSizeImage[1]['w']}px; height: {$aSizeImage[1]['h']}px;\" /></a>",
        ];

        /* #2. Реальные размеры = Пришедшие размеры, есть $sAddClass */
        $aProvider[] = [
                self::$iSectionId,
                "<a href=\"{$sImgSrc}\" class=\"{$sAddClass}\" data-fancybox-group=\"button\" ><img alt=\"\" src=\"{$sNewName}\" style=\"margin:5px; width: {$aSizeImage[2]['w']}px; height: {$aSizeImage[2]['h']}px;\" /></a>",
                "<a href=\"{$sImgSrc}\" class=\"{$sAddClass}\" data-fancybox-group=\"button\" ><img alt=\"\" src=\"{$sNewName}\" style=\"margin:5px; width: {$aSizeImage[2]['w']}px; height: {$aSizeImage[2]['h']}px;\" /></a>",
        ];

        /* #3. Реальные размеры <> Пришедшие размеры, есть $sAddClass */
        $aProvider[] = [
                self::$iSectionId,
                "<a href=\"{$sImgSrc}\" class=\"{$sAddClass}\" data-fancybox-group=\"button\" ><img alt=\"\" src=\"{$sNewName}\" style=\"margin:5px; width: {$aSizeImage[3]['w']}px; height: {$aSizeImage[3]['h']}px;\" /></a>",
                "<a href=\"{$sImgSrc}\" class=\"{$sAddClass}\" data-fancybox-group=\"button\" ><img alt=\"\" src=\"{$sNewName}\" style=\"margin:5px; width: {$aSizeImage[3]['w']}px; height: {$aSizeImage[3]['h']}px;\" /></a>",
        ];

        /* #4. Реальные размеры = Пришедшие размеры(указаны в процентах), нет $sAddClass */
        $aProvider[] = [
            self::$iSectionId,
            "<img alt=\"\" src=\"{$sImgSrc}\" style=\"margin:5px; width: {$aSizeImage[4]['w']}%; height: {$aSizeImage[4]['h']}%;\" />",
            "<img alt=\"\" src=\"{$sImgSrc}\" style=\"margin:5px; width: {$aSizeImage[4]['w']}%; height: {$aSizeImage[4]['h']}%;\" />",
        ];

        $sNewName = self::getResizeFileName($sRealName, round($iRealW * $aSizeImage[5]['w'] / 100), round($iRealH * $aSizeImage[5]['w'] / 100), $sFileExtension);
        /* #5. Реальные размеры <> Пришедшие размеры(указаны в процентах), нет $sAddClass */
        $aProvider[] = [
            self::$iSectionId,
            "<img alt=\"\" src=\"{$sImgSrc}\" style=\"margin:5px; width: {$aSizeImage[5]['w']}%; height: {$aSizeImage[5]['w']}%;\" />",
            "<a href=\"{$sImgSrc}\" class=\"{$sAddClass}\" data-fancybox=\"button\" ><img alt=\"\" src=\"{$sNewName}\" style=\"margin:5px; width: {$aSizeImage[5]['w']}%; height: {$aSizeImage[5]['h']}%;\" /></a>",
        ];

        /* #6. Есть блокирующий класс */
        $aProvider[] = [
            self::$iSectionId,
            "<img class=\"sk-block-crop\" alt=\"\" src=\"{$sImgSrc}\" style=\"margin:5px; width: {$aSizeImage[5]['w']}%; height: {$aSizeImage[5]['w']}%;\" />",
            "<img class=\"sk-block-crop\" alt=\"\" src=\"{$sImgSrc}\" style=\"margin:5px; width: {$aSizeImage[5]['w']}%; height: {$aSizeImage[5]['w']}%;\" />",
        ];

        return $aProvider;
    }

    public static function provider4RestoreTags()
    {
        return [
            // 1. Ccылки на реальные ресурсы оставляем
            [
                '<a href="#"><img alt="" src="/files/3/resize/left-icon-fb_61_61_88_88.png" /></a>',
                '<a href="#"><img alt="" src="/files/3/resize/left-icon-fb_61_61_88_88.png" /></a>',
            ],
            // 2. Ссылки со спец классом убираем
            [
                '<a href="/files/3/resize/left-icon-fb_61_61_88_88_37_37.png" class="js_use_resize b-use-resize" data-fancybox-group="button" ><img alt="" height="20" src="/files/3/resize/left-icon-fb_61_61_88_88_37_37_20_20.png" width="20" /></a>',
                '<img alt="" height="20" src="/files/3/resize/left-icon-fb_61_61_88_88_37_37.png" width="20" />',
            ],
        ];
    }

    public function setUp()
    {
        $sDir = WEBPATH . 'files/' . self::$iSectionId;
        if (!file_exists($sDir)) {
            mkdir($sDir);
        }
        copy(__DIR__ . \DIRECTORY_SEPARATOR . self::$sSourceDir . \DIRECTORY_SEPARATOR . self::$sTestFile, self::getFullImageName());
    }

    public function tearDown()
    {
        $sDir = WEBPATH . 'files/' . self::$iSectionId;
        if (file_exists($sDir)) {
            FileHelper::removeDirectory($sDir);
        }
    }

    /**
     * @dataProvider provider4WrapTags
     * @covers \skewer\helpers\ImageResize::wrapTags
     *
     * @param mixed $iSectionId4Save
     * @param mixed $sInHTML
     * @param mixed $sOutHTML
     */
    public function testWrapTags($iSectionId4Save, $sInHTML, $sOutHTML)
    {
        self::assertSame($sOutHTML, ImageResize::wrapTags($sInHTML, $iSectionId4Save));
    }

    /**
     * @dataProvider provider4RestoreTags
     * @covers \skewer\helpers\ImageResize::restoreTags
     *
     * @param mixed $sInHTML
     * @param mixed $sOutHTML
     */
    public function testRestoreTags($sInHTML, $sOutHTML)
    {
        self::assertSame($sOutHTML, ImageResize::restoreTags($sInHTML));
    }
}
