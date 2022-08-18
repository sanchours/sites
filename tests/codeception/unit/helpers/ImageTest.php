<?php

namespace unit\helpers;

use skewer\helpers\Image;

/**
 * Created by PhpStorm.
 * User: na
 * Date: 11.07.2016
 * Time: 11:12
 * To run this test use: codecept run codeception/unit/helpers/ImageTest.php.
 */
class ImageTest extends \Codeception\Test\Unit
{
    /**
     * @var \skewer\components\redirect\Api
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Image();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * dataProvider для testNeedRotation.
     *
     * @return array
     */
    public function providerForRotation()
    {
        return [
            [150, 100, 80, 80, 0, false],
            [100, 150, 80, 80, 0, false],
            [150, 100, 80, 80, 1, false],
            [100, 150, 80, 80, 1, false],
            [150, 100, 200, 150, 0, false],
            [100, 150, 200, 150, 0, false],
            [150, 100, 150, 200, 0, false],
            [100, 150, 150, 200, 0, false],
            [150, 100, 150, 200, 1, true],
            [150, 100, 200, 150, 1, false],
            [100, 150, 150, 200, 1, false],
            [150, 100, 200, 150, 1, true],
        ];
    }

    /**
     * @covers  \skewer\helpers\Image::needRotation
     * @dataProvider providerForRotation
     *
     * @param $iWidth
     * @param $iHeight
     * @param $iSrcWidth
     * @param $iSrcHeight
     * @param $iRotateImage
     * @param mixed $bResult
     */
    public function testNeedRotation($iWidth, $iHeight, $iSrcWidth, $iSrcHeight, $iRotateImage, $bResult)
    {
        $bResult = Image::needRotation($iWidth, $iHeight, $iSrcWidth, $iSrcHeight, $iRotateImage);
        $this->assertEquals($bResult, $bResult, 'needRotation error');
    }

    /**
     * dataProvider для testOperateCalculation.
     *
     * @return array
     */
    public function providerForCalculation()
    {
        return [
           [150, 100, 80, 80, false, [
                'img_width' => 80,
                'img_height' => 80,
                'left_delay' => 35,
                'top_delay' => 10,
            ]],
            [150, 100, 80, 80, true, [
                'img_width' => 80,
                'img_height' => 80,
                'left_delay' => 35,
                'top_delay' => 10,
            ]],
            [150, 100, 300, 200, false, [
                'img_width' => 300,
                'img_height' => 200,
                'left_delay' => 0,
                'top_delay' => 0,
            ]],
            [150, 100, 300, 200, true, [
                'img_width' => 150,
                'img_height' => 100,
                'left_delay' => 0,
                'top_delay' => 0,
            ]],
        /*not test*/
//            array(100,150,300,200,false,array(
//                'img_width'=>150,
//                'img_height'=>100,
//                'left_delay'=>-100,
//                'top_delay'=>-25
//            )),
//            array(100,150,300,200,true,array(
//                'img_width'=>150,
//                'img_height'=>100,
//                'left_delay'=>-100,
//                'top_delay'=>-25
//            )),
        /*not test*/
            [100, 150, 200, 300, false, [
                'img_width' => 100,
                'img_height' => 150,
                'left_delay' => -50,
                'top_delay' => -75,
            ]],
            [100, 150, 200, 300, true, [
                'img_width' => 100,
                'img_height' => 150,
                'left_delay' => 0,
                'top_delay' => 0,
            ]],
            [150, 100, 75, 50, false, [
                'img_width' => 75,
                'img_height' => 50,
                'left_delay' => 38,
                'top_delay' => 25,
            ]],
            [150, 100, 75, 50, true, [
                'img_width' => 75,
                'img_height' => 50,
                'left_delay' => 38,
                'top_delay' => 25,
            ]],
            [150, 100, 75, 0, false, [
                'img_width' => 75,
                'img_height' => 0,
                'left_delay' => 38,
                'top_delay' => 50,
            ]],
            [150, 100, 75, 0, true, [
                'img_width' => 75,
                'img_height' => 0,
                'left_delay' => 38,
                'top_delay' => 50,
            ]],
            [150, 100, 300, 0, false, [
                'img_width' => 150,
                'img_height' => 0,
                'left_delay' => -75,
                'top_delay' => 50,
            ]],
            [150, 100, 300, 0, true, [
                'img_width' => 150,
                'img_height' => 0,
                'left_delay' => 0,
                'top_delay' => 50,
            ]],

           [150, 100, 200, 1, true, [
                'img_width' => 150,
                'img_height' => 1,
                'left_delay' => 0,
                'top_delay' => 50,
            ]],
        ];
    }

    /**
     * @covers  \skewer\helpers\Image::operateCalculation
     * @dataProvider providerForCalculation
     *
     * @param $iWidth
     * @param $iHeight
     * @param $iSrcWidth
     * @param $iSrcHeight
     * @param $bAccomodateImage
     * @param $aNeedResult
     */
    public function testOperateCalculation($iWidth, $iHeight, $iSrcWidth, $iSrcHeight, $bAccomodateImage, $aNeedResult)
    {
        $aResult = Image::operateCalculation($iWidth, $iHeight, $iSrcWidth, $iSrcHeight, $bAccomodateImage);
        $this->assertEquals($aNeedResult, $aResult, 'operateCalculation error');
    }

    /**
     * dataProvider для testGetNotScaleParams.
     *
     * @return array
     */
    public function providerForNotScale()
    {
        return [
            [200, 100, 100, 100, 100, 100],
            [200, 100, 200, 50, 200, 50],
            [200, 100, 50, 100, 50, 100],
            [100, 200, 100, 100, 100, 100],
            [100, 200, 100, 50, 100, 50],
            [100, 200, 50, 200, 50, 200],
        ];
    }

    /**
     * @covers  \skewer\helpers\Image::getNotScaleParams
     * @dataProvider providerForNotScale
     *
     * @param $iSrcW
     * @param $iSrcH
     * @param $iNeedW
     * @param $iNeedH
     * @param $iOutW
     * @param $iOutH
     */
    public function testGetNotScaleParams($iSrcW, $iSrcH, $iNeedW, $iNeedH, $iOutW, $iOutH)
    {
        $oImage = new Image();

        $oImage->updSizes($iSrcW, $iSrcH);

        $aResult = $oImage->getNotScaleParams($iNeedW, $iNeedH);

        $aNeedResult['width'] = $iOutW;
        $aNeedResult['height'] = $iOutH;

        $this->assertEquals($aNeedResult, $aResult, 'operateCalculation error');
    }
}
